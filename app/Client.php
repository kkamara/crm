<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;
use App\User;

class Client extends Model
{
    /** 
     * This models immutable values.
     *
     * @var array 
     */
    protected $guarded = [];

    /**
     * Set a publicily accessible identifier to get the path for this unique instance.
     * 
     * @return  string
     */
    public function getPathAttribute()
    {
        return url('/').'/clients/'.$this->slug;
    }

    /**
     * Set a publicily accessible identifier to get the image path for this unique instance.
     * 
     * @return  string
     */
    public function getImageAttribute()
    {
        $image = $this->attributes['image'];
        $slug  = $this->attributes['slug'];

        $imagePath = sprintf('uploads/clients/%s/%s', $slug, $image);

        if(File::exists(public_path($imagePath)))
        {
            return asset($imagePath);
        }

        return asset('images/client-avatar.jpg');
    }

    /**
     * This model relationship belongs to \App\User.
     * 
     * @return  \Illuminate\Database\Eloquent\Model
     */
    public function user()
    {
        return $this->belongsTo('App\User', 'user_created');
    }

    /**
     * Set a publicily accessible identifier to get the updated by for this unique instance.
     * 
     * @return  string
     */
    public function getUpdatedByAttribute()
    {
        $user = User::where('id', $this->user_modified)->first();

        return $user;
    }

    /**
     * Set a publicily accessible identifier to get the name for this unique instance.
     * 
     * @return  string
     */
    public function getNameAttribute()
    {
        $name = trim($this->attributes['first_name'].' '.$this->attributes['last_name']);

        return !empty($name) ? $name : 'None';
    }

    /**
     * Uses request data to update an instance of this model.
     * 
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $imageName  (optional)
     * @return bool
     */
    public function updateClient($request, $imageName = null)
    {
        $this->user_modified = auth()->user()->id;
        $this->first_name = !empty($request->input('first_name')) ? trim(filter_var($request->input('first_name'), FILTER_SANITIZE_STRING)) : NULL;
        $this->last_name = !empty($request->input('last_name')) ? trim(filter_var($request->input('last_name'), FILTER_SANITIZE_STRING)) : NULL;
        $this->email = trim(filter_var($request->input('email'), FILTER_SANITIZE_EMAIL));
        $this->building_number = trim(filter_var($request->input('building_number'), FILTER_SANITIZE_STRING));
        $this->street_name = trim(filter_var($request->input('street_name'), FILTER_SANITIZE_STRING));
        $this->postcode = trim(filter_var($request->input('postcode'), FILTER_SANITIZE_STRING));
        $this->city = trim(filter_var($request->input('city'), FILTER_SANITIZE_STRING));
        $this->contact_number = !empty($request->input('contact_number')) ? trim(filter_var($request->input('contact_number'), FILTER_SANITIZE_STRING)) : NULL;
        $this->image = isset($imageName) ? $imageName : NULL;

        return $this->save() ? TRUE : FALSE;
    }
    
    /**
     * Adds onto a query parameters provided in request to search for items of this instance.
     * 
     * @param  \Illuminate\Database\Eloquent\Model  $query
     * @param  \Illuminate\Http\Request             $request
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function scopeSearch($query, $request)
    {
        if(array_key_exists('company', $request) || array_key_exists('representative', $request) || array_key_exists('email', $request) || array_key_exists('created_at', $request) || array_key_exists('updated_at', $request))
        {
            if(array_key_exists('search', $request))
            {
                $searchParam = filter_var($request['search'], FILTER_SANITIZE_STRING);

                if(array_key_exists('company', $request))
                {
                    $query = $query->where('company', 'like', '%'.$searchParam.'%');
                }
                if(array_key_exists('representative', $request))
                {
                    $fullName = explode(' ', $searchParam);

                    if(sizeof($fullName) == 2)
                    {
                        $query = $query->where('first_name', 'like', '%'.$fullName[0].'%');
                        $query = $query->where('last_name', 'like', '%'.$fullName[1].'%');
                    }
                    else
                    {
                        $query = $query->where('first_name', 'like', '%'.$fullName[0].'%');
                    }
                }
                if(array_key_exists('email', $request))
                {
                    $query = $query->where('email', 'like', '%'.$searchParam.'%');
                }
                if(array_key_exists('created_at', $request))
                {
                    $query = $query->whereDate('created_at', 'like', '%'.$searchParam.'%');
                }
                if(array_key_exists('updated_at', $request))
                {
                    $query = $query->whereDate('updated_at', 'like', '%'.$searchParam.'%');
                }
            }
        }
        else
        {
            if(array_key_exists('search', $request))
            {
                $searchParam = filter_var($request['search'], FILTER_SANITIZE_STRING);

                $query = $query->where('company', 'like', '%'.$searchParam.'%')
                                ->orWhere('first_name', 'like', '%'.$searchParam.'%')
                                ->orWhere('last_name', 'like', '%'.$searchParam.'%')
                                ->orWhere('email', 'like', '%'.$searchParam.'%')
                                ->orWhere('created_at', 'like', '%'.$searchParam.'%')
                                ->orWhere('updated_at', 'like', '%'.$searchParam.'%');
            }
        }

        return $query;
    }

    /**
     *  Get clients available to a given user.
     *
     *  @param  \App\User $user
     *  @return \Illuminate\Database\Eloquent\Model
     */
    public function scopeGetAccessibleClients($query, $user)
    {
        return $query->select(
                'clients.slug', 'clients.first_name', 'clients.last_name', 'clients.email', 
                'clients.created_at', 'clients.updated_at', 'clients.id', 'clients.company',
                'clients.contact_number', 'clients.building_number', 'clients.street_name',
                'clients.city', 'clients.postcode'
            )
            ->leftJoin('client_user', 'clients.id', '=', 'client_user.client_id')
            ->where('client_user.user_id', '=', $user->id)
            ->groupBy('clients.id');
    }

    /**
     *  Check whether user has access to a given array of client ids.
     *
     *  @param  array  $clientIds
     *  @param  \App\User $user
     *  @return bool
     */
    public static function hasAccessToClients($clientIds, $user)
    {
        $clients = self::getAccessibleClients($user)
            ->whereIn('clients.id', $clientIds)
            ->get()
            ->toArray();

        foreach($clients as $c)
        {
            if(!in_array($c['id'], $clientIds))
            {
                return false;
            }
        }

        return true;
    }
}
