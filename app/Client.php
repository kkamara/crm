<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    public function path()
    {
        return url('/').'/clients/'.$this->id;
    }
}
