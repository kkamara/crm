<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\User;
use Validator;

class Log extends Model
{
    //

    public function path()
    {
        return url('/').'/logs/'.$this->id;
    }

    public function user()
    {
        return $this->belongsTo('App\User', 'user_created');
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
            'body'  => 'required|min:20',
            'user_modified' => 'required|exists:users,id',
        ]);

        return $validator->errors()->all();
    }

    public function sanitizeData($request)
    {
        $data = array();
        $data['title']         = filter_var(request('title'), FILTER_SANITIZE_STRING);
        $data['description']   = filter_var(request('description'), FILTER_SANITIZE_STRING);
        $data['body']          = filter_var(request('body'), FILTER_SANITIZE_STRING);
        $data['user_modified'] = filter_var(request('user_modified'), FILTER_SANITIZE_STRING);
        $data['notes']         = filter_var(request('notes'), FILTER_SANITIZE_STRING);

        return $data;
    }

    public function updateLog($data)
    {
        $this->title         = $data['title'];
        $this->description   = $data['description'];
        $this->body          = $data['body'];
        $this->user_modified = $data['user_modified'];
        $this->notes         = $data['notes'];
        
        return ($this->save()) ? TRUE : FALSE;
    }
}
