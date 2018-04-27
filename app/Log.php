<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\User;
use Validator;

class Log extends Model
{
    protected $guarded = [];

    public function path()
    {
        return url('/').'/logs/'.$this->slug;
    }

    public function user()
    {
        return $this->belongsTo('App\User', 'user_created');
    }

    public function client()
    {
        return $this->belongsTo('App\Client', 'client_id');
    }

    public function getUpdatedByAttribute()
    {
        $user = User::where('id', $this->user_modified)->first();

        return $user;
    }

    public function validationErrors($request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|max:191|min:5',
            'description'  => 'required|min:20',
            'body'  => 'required|min:20'
        ]);

        return $validator->errors()->all();
    }

    public function parseData($request)
    {
        $data = array();
        $data['title']         = request('title');
        $data['description']   = request('description');
        $data['body']          = request('body');
        $data['notes']         = request('notes');

        return $data;
    }

    public function updateLog($data)
    {
        $this->user_modified = auth()->user()->id;
        $this->title         = trim(filter_var($data['title'], FILTER_SANITIZE_STRING));
        $this->description   = trim(filter_var($data['description'], FILTER_SANITIZE_STRING));
        $this->body          = trim(filter_var($data['body'], FILTER_SANITIZE_STRING));
        $this->notes         = trim(filter_var($data['notes'], FILTER_SANITIZE_STRING));

        return ($this->save()) ? TRUE : FALSE;
    }

    public function getDescriptionAttribute()
    {
        return nl2br(e($this->attributes['description']));
    }

    public function getBodyAttribute()
    {
        return nl2br(e($this->attributes['body']));
    }

    public function getNotesAttribute()
    {
        return nl2br(e($this->attributes['notes']));
    }

    public function getEditDescriptionAttribute()
    {
        $desc = str_replace('<br/>', '', $this->attributes['description']);

        return e($desc);
    }

    public function getEditBodyAttribute()
    {
        $body = str_replace('<br/>', '', $this->attributes['body']);

        return e($body);
    }

    public function getEditNotesAttribute()
    {
        $notes = str_replace('<br/>', '', $this->attributes['notes']);

        return e($notes);
    }

    public function getShortDescriptionAttribute()
    {
        $desc = str_replace('<br/>', '', $this->attributes['description']);

        return strlen($desc) > 300 ? substr($desc,0,299).'...' : $desc;
    }

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
}
