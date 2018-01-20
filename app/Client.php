<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;
use App\User;

class Client extends Model
{
    protected $guarded = [];

    public function path()
    {
        return url('/').'/clients/'.$this->id;
    }

    public function getImageAttribute()
    {
        $image = $this->attributes['image'];
        $imagePath = 'uploads/clients/'.$this->attributes['id'].'/'.$image;
        
        if(File::exists($imagePath))
        {
            return asset($imagePath);
        }

        return asset('images/client-avatar.jpg');
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

    // public function validationErrors($request)
    // {
    //     $validator = $request->validate([
    //         'user_id' => 'required|max:191|min:5',
    //         'description'  => 'required|min:20',
    //         'body'  => 'required|min:20',
    //         'user_modified' => 'required|exists:users,id',
    //     ]);

    //     return $validator->errors()->all();
    // }

    // public function parseData($request)
    // {
    //     $data = array();
    //     $data['title']         = request('title');
    //     $data['description']   = request('description');
    //     $data['body']          = request('body');
    //     $data['user_modified'] = request('user_modified');
    //     $data['notes']         = request('notes');

    //     return $data;
    // }
}
