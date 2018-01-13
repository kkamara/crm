<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;
use App\User;

class Client extends Model
{
    

    public function path()
    {
        return url('/').'/clients/'.$this->id;
    }

    public function getImageAttribute()
    {
        $imagePath = $this->attributes['image'];
        
        if(File::exists($imagePath))
        {
            return asset($imagePath);
        }

        return asset('storage/images/client-avatar.jpg');
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
}
