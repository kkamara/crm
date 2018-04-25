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

        if(File::exists(public_path($imagePath)))
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

    public function getNameAttribute()
    {
        $name = trim($this->attributes['first_name'].' '.$this->attributes['last_name']);

        return !empty($name) ? $name : 'None';
    }

    public function updateClient($imageName = null)
    {
        $this->user_modified = auth()->user()->id;
        $this->first_name = !empty(request('first_name')) ? trim(filter_var(request('first_name'), FILTER_SANITIZE_STRING)) : NULL;
        $this->last_name = !empty(request('last_name')) ? trim(filter_var(request('last_name'), FILTER_SANITIZE_STRING)) : NULL;
        $this->email = trim(filter_var(request('email'), FILTER_SANITIZE_EMAIL));
        $this->building_number = trim(filter_var(request('building_number'), FILTER_SANITIZE_STRING));
        $this->street_name = trim(filter_var(request('street_name'), FILTER_SANITIZE_STRING));
        $this->postcode = trim(filter_var(request('postcode'), FILTER_SANITIZE_STRING));
        $this->city = trim(filter_var(request('city'), FILTER_SANITIZE_STRING));
        $this->contact_number = !empty(request('contact_number')) ? trim(filter_var(request('contact_number'), FILTER_SANITIZE_STRING)) : NULL;
        $this->image = isset($imageName) ? $imageName : NULL;

        return $this->save() ? TRUE : FALSE;
    }
}
