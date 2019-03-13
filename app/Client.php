<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\MessageBag;
use Validator;
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
     * This model relationship belongs to \App\User.
     * 
     * @return  \Illuminate\Database\Eloquent\Model
     */
    public function userUpdated()
    {
        return $this->belongsTo('App\User', 'user_modified');
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
     * Update db instance of this model.
     * 
     * @param  array $data
     * @param  \App\User $user 
     * @return Client 
     */
    public function updateClient($data, $user)
    {
        if(Input::hasFile('image'))
        {
            $file = Input::file('image');
            $imageName = $file->getClientOriginalName();
        }

        $this->update([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'building_number' => $data['building_number'],
            'street_name' => $data['street_name'],
            'city' => $data['city'],
            'postcode' => $data['postcode'],
            // optional
            'contact_number' => $data['contact_number'],
            'user_modified' => $user->id,
            'image' => $imageName ?? null,
        ]);

        if(isset($file) && isset($imageName))
        {
            $file->move(public_path('uploads/clients/'.$this->slug), $imageName);
        }

        return $this;
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
        if($request->has('company') || $request->has('representative') || $request->has('email') || $request->has('created_at') || $request->has('updated_at'))
        {
            if($request->has('search'))
            {
                $searchParam = filter_var($request['search'], FILTER_SANITIZE_STRING);

                if($request->has('company'))
                {
                    $query = $query->where('company', 'like', '%'.$searchParam.'%');
                }
                if($request->has('representative'))
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
                if($request->has('email'))
                {
                    $query = $query->where('email', 'like', '%'.$searchParam.'%');
                }
                if($request->has('created_at'))
                {
                    $query = $query->whereDate('created_at', 'like', '%'.$searchParam.'%');
                }
                if($request->has('updated_at'))
                {
                    $query = $query->whereDate('updated_at', 'like', '%'.$searchParam.'%');
                }
            }
        }
        else
        {
            if($request->has('search'))
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

    /**
     *  Get data for store request
     *
     *  @param \Illuminate\Http\Request $request
     *  @return array
     */
    public static function getStoreData($request)
    {
        if($cu = $request->input('client_users'))
        {
            $clientUsers = [];

            for($i=0;$i<count($cu);$i++)
            {
                $clientUsers[] = filter_var($cu[$i], FILTER_SANITIZE_STRING);
            }
        }

        return [
            'first_name' => $request->input('first_name'),
            'last_name' => $request->input('last_name'),
            'company' => $request->input('company'),
            'email' => $request->input('email'),
            'building_number' => $request->input('building_number'),
            'street_name' => $request->input('street_name'),
            'city' => $request->input('city'),
            'postcode' => $request->input('postcode'),
            'contact_number' => $request->input('contact_number'),
            'image' => $request->input('image'),
            'client_users' => $clientUsers ?? NULL,
        ];
    }

    /**
     *  Get store errors
     *
     *  @param  array $data
     *  @param  \App\User $user
     *  @return \Illuminate\Support\MessageBag
     */
    public static function getStoreErrors($data, $user)
    {
        $errors = new MessageBag;

        $validator = Validator::make($data, [
            'first_name' => 'max:191',
            'last_name' => 'max:191',
            'company' => 'required|max:191|min:3|unique:clients',
            'email' => 'required|email|max:191',
            'building_number' => 'required|max:191',
            'street_name' => 'required|max:191',
            'city' => 'required|max:191',
            'postcode' => 'required|max:191',
            // optional
            'contact_number' => 'max:191',
            'client_users' => 'required|array',
            'client_users.*' => 'required|integer',
        ]);

        if(isset($data['client_users']))
        {
            if(!Client::hasAccessToClients($data['client_users'], $user))
            {
                $errors->add("client_users", "No client selected");
            }
        }   

        if(Input::hasFile('image'))
        {
            $file = Input::file('image');
            $mimetype = $file->getClientMimeType();

            switch($mimetype)
            {
                case "image/jpeg":
                case "image/png":
                case "image/tiff";
                break;
                default:
                    $errors->add("image", "File must be of the following type: jpeg, png, tiff");
                break;
            }
        }

        return $validator->messages()->merge($errors);
    }

    /**
     *  Clean store data
     *
     *  @param array $data
     *  @return array
     */
    public static function cleanStoreData($data)
    {
        return [
            'first_name' => filter_var($data['first_name'], FILTER_SANITIZE_STRING),
            'last_name' => filter_var($data['last_name'], FILTER_SANITIZE_STRING),
            'company' => filter_var($data['company'], FILTER_SANITIZE_STRING),
            'email' => filter_var($data['email'], FILTER_SANITIZE_STRING),
            'building_number' => filter_var($data['building_number'], FILTER_SANITIZE_STRING),
            'street_name' => filter_var($data['street_name'], FILTER_SANITIZE_STRING),
            'city' => filter_var($data['city'], FILTER_SANITIZE_STRING),
            'postcode' => filter_var($data['postcode'], FILTER_SANITIZE_STRING),
            // optional
            'contact_number' => filter_var($data['contact_number'], FILTER_SANITIZE_STRING),
            'image' => $data['image'],
            'client_users' => $data['client_users'],
        ];
    }

    /**
     * Create db instance of this model
     *
     *  @param  array $data
     *  @param  \App\User $user
     *  @return \App\Client
     */
    public function createClient($data, $user)
    {
        // get image name
        if(Input::hasFile('image'))
        {
            $file = Input::file('image');
            $imageName = $file->getClientOriginalName();
        }

        $client = Client::create([
            'user_created' => $user->id,
            'slug' => strtolower(str_slug($data['company'], '-')),
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'company' => $data['company'],
            'email' => $data['email'],
            'building_number' => $data['building_number'],
            'street_name' => $data['street_name'],
            'postcode' => $data['postcode'],
            'city' => $data['city'],
            'contact_number' => $data['contact_number'],
            'image' => $imageName ?? null,
        ]);

        // store image file if provided
        if(isset($file) && isset($imageName))
        {
            $file->move(public_path('uploads/clients/'.$client->slug), $imageName);
        }

        // assign users to clients
        $limit = count($data['client_users']);

        for($i=0;$i<$limit;$i++)
        {
            $clientUsers = [
                'user_id' => $data['client_users'][$i],
                'client_id' => $client->id,
            ];
            DB::table('client_user')->insert($clientUsers);

            $clientUsers = [
                'user_id' => $user->id,
                'client_id' => $client->id,
            ];
            DB::table('client_user')->insert($clientUsers);
        }

        return $client;
    }

    /**
     *  Get data for update request for this resource.
     *
     *  @param  \Illuminate\Http\Request $request
     *  @return array
     */
    public static function getUpdateData($request)
    {
        return [
            'first_name' => $request->input('first_name'),
            'last_name' => $request->input('last_name'),
            'email' => $request->input('email'),
            'building_number' => $request->input('building_number'),
            'street_name' => $request->input('street_name'),
            'city' => $request->input('city'),
            'postcode' => $request->input('postcode'),
            // optional
            'contact_number' => $request->input('contact_number'),
        ];
    }

    /**
     *  Sanitize update data.
     *
     *  @param  array $data
     *  @return array
     */
    public static function cleanUpdateData($data)
    {
        return [
            'first_name' => filter_var($data['first_name'], FILTER_SANITIZE_STRING),
            'last_name' => filter_var($data['last_name'], FILTER_SANITIZE_STRING),
            'email' => filter_var($data['email'], FILTER_SANITIZE_EMAIL),
            'building_number' => filter_var($data['building_number'], FILTER_SANITIZE_STRING),
            'street_name' => filter_var($data['street_name'], FILTER_SANITIZE_STRING),
            'city' => filter_var($data['city'], FILTER_SANITIZE_STRING),
            'postcode' => filter_var($data['postcode'], FILTER_SANITIZE_STRING),
            // optional
            'contact_number' => filter_var($data['contact_number'], FILTER_SANITIZE_STRING),
        ];
    }

    /**
     *  Get update errors.
     *
     *  @param array $data
     *  @return array
     */
    public static function getUpdateErrors($data)
    {
        $errors = new MessageBag;

        $validator = Validator::make($data, [
            'first_name' => 'max:191',
            'last_name' => 'max:191',
            'email' => 'required|email|max:191',
            'building_number' => 'required|max:191',
            'street_name' => 'required|max:191',
            'city' => 'required|max:191',
            'postcode' => 'required|max:191',
            // optional
            'contact_number' => 'max:191',
        ]);

        if(Input::hasFile('image'))
        {
            $file = Input::file('image');
            $mimetype = $file->getClientMimeType();

            switch($mimetype)
            {
                case "image/jpeg":
                case "image/png":
                case "image/tiff";
                break;
                default:
                    $errors->add("image", "File must be of the following type: jpeg, png, tiff");
                break;
            }
        }

        return $validator->messages()->merge($errors);
    }
}
