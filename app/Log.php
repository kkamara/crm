<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\User;
use Validator;

class Log extends Model
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
        return url('/').'/logs/'.$this->slug;
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
     * This model relationship belongs to \App\Client.
     * 
     * @return  \Illuminate\Database\Eloquent\Model
     */
    public function client()
    {
        return $this->belongsTo('App\Client', 'client_id');
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
     * Get validation errors for an operation that creates or updates values of an instance of this model.
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function validationErrors($request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|max:191|min:5',
            'description'  => 'required|min:20',
            'body'  => 'required|min:20'
        ]);

        return $validator->errors()->all();
    }

    /**
     * Gets request data for an operation that creates or updates values of an instance of this model.
     * 
     * @param  \Illuminate\Http\Request  $request
     */
    public function parseData($request)
    {
        $data = array();
        $data['title']       = request('title');
        $data['description'] = request('description');
        $data['body']        = request('body');
        $data['notes']       = request('notes');

        return $data;
    }

    /**
     * Updates values of this instance.
     * 
     * @param  array  $data
     * @return bool
     */
    public function updateLog($data)
    {
        $this->user_modified = auth()->user()->id;
        $this->title         = trim(filter_var($data['title'], FILTER_SANITIZE_STRING));
        $this->description   = trim(filter_var($data['description'], FILTER_SANITIZE_STRING));
        $this->body          = trim(filter_var($data['body'], FILTER_SANITIZE_STRING));
        $this->notes         = trim(filter_var($data['notes'], FILTER_SANITIZE_STRING));

        return ($this->save()) ? TRUE : FALSE;
    }

    /**
     * Set a publicily accessible identifier to get the description for this unique instance.
     * 
     * @return  string
     */
    public function getDescriptionAttribute()
    {
        return nl2br(e($this->attributes['description']));
    }

    /**
     * Set a publicily accessible identifier to get the body for this unique instance.
     * 
     * @return  string
     */
    public function getBodyAttribute()
    {
        return nl2br(e($this->attributes['body']));
    }

    /**
     * Set a publicily accessible identifier to get the notes for this unique instance.
     * 
     * @return  string
     */
    public function getNotesAttribute()
    {
        return nl2br(e($this->attributes['notes']));
    }

    /**
     * Set a publicily accessible identifier to get the edit description for this unique instance.
     * 
     * @return  string
     */
    public function getEditDescriptionAttribute()
    {
        $desc = str_replace('<br/>', '', $this->attributes['description']);

        return e($desc);
    }

    /**
     * Set a publicily accessible identifier to get the edit body for this unique instance.
     * 
     * @return  string
     */
    public function getEditBodyAttribute()
    {
        $body = str_replace('<br/>', '', $this->attributes['body']);

        return e($body);
    }

    /**
     * Set a publicily accessible identifier to get the edit notes for this unique instance.
     * 
     * @return  string
     */
    public function getEditNotesAttribute()
    {
        $notes = str_replace('<br/>', '', $this->attributes['notes']);

        return e($notes);
    }

    /**
     * Set a publicily accessible identifier to get the short description for this unique instance.
     * 
     * @return  string
     */
    public function getShortDescriptionAttribute()
    {
        $desc = str_replace('<br/>', '', $this->attributes['description']);

        return strlen($desc) > 300 ? substr($desc,0,299).'...' : $desc;
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
        if(array_key_exists('title', $request) || array_key_exists('desc', $request) || array_key_exists('body', $request) || array_key_exists('created_at', $request) || array_key_exists('updated_at', $request))
        {
            if(array_key_exists('search', $request))
            {
                $searchParam = filter_var($request['search'], FILTER_SANITIZE_STRING);

                if(array_key_exists('title', $request))
                {
                    $query = $query->where('title', 'like', '%'.$searchParam.'%');
                }
                if(array_key_exists('desc', $request))
                {
                    $query = $query->where('description', 'like', '%'.$searchParam.'%');
                }
                if(array_key_exists('body', $request))
                {
                    $query = $query->where('body', 'like', '%'.$searchParam.'%');
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
                $searchParam = filter_var(request('search'), FILTER_SANITIZE_STRING);

                $query = $query->where('title', 'like', '%'.$searchParam.'%')
                                ->orWhere('description', 'like', '%'.$searchParam.'%')
                                ->orWhere('body', 'like', '%'.$searchParam.'%')
                                ->orWhere('created_at', 'like', '%'.$searchParam.'%')
                                ->orWhere('updated_at', 'like', '%'.$searchParam.'%');
            }
        }

        return $query;
    }

    /**
     *  Get logs available to a given user.
     *
     *  @param \App\User $user
     *  @return \Illuminate\Database\Eloquent\Model
     */
    public static function getUserAccessibleLogs($user)
    {
        return self::select(
                'logs.id', 'logs.slug', 'logs.title', 'logs.description', 'logs.created_at', 'logs.updated_at'
            )
            ->leftJoin('clients', 'logs.id', '=', 'clients.id')
            ->leftJoin('client_user', 'clients.id', '=', 'client_user.id')
            ->where('client_user.user_id', '=', $user->id)
            ->orderBy('id', 'desc')
            ->paginate(10);        
    }
}
