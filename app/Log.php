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

    public function parseData($request)
    {
        $data = array();
        $data['title']         = request('title');
        $data['description']   = request('description');
        $data['body']          = request('body');
        $data['user_modified'] = request('user_modified');
        $data['notes']         = request('notes');

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
}
