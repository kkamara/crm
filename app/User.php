<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\Permission\Traits\HasRoles;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class User extends Authenticatable
{
    use Notifiable;
    use HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public function getLastLoginAttribute()
    {
        if(empty($this->attributes['last_login']))
        {
            return '';
        }

        return Carbon::parse($this->attributes['last_login'])->diffForHumans();
    }

    public function getNameAttribute()
    {
        return $this->attributes['first_name'] . ' ' . $this->attributes['last_name'];
    }

    public function getUserClients()
    {
        $userClients = DB::table('client_user')->select('client_id', 'user_id')->where('user_id', $this->id)->get();

        return $userClients;
    }

    public function getClientUsers()
    {
        $userClients = DB::table('client_user')->select('client_id', 'user_id')->where('user_id', $this->id)->get();

        $userIds = array();

        foreach($userClients as $userClient)
        {
            array_push($userIds, $userClient->user_id);
        }

        $clientUsers = User::whereIn('id', $userIds)->orderBy('first_name', 'ASC')->get();

        return $clientUsers;
    }

    public function getClientSpecificUsers($clientId)
    {
        $clientUsers = DB::table('client_user')->where(['client_id'=>$clientId])->get();

        $userIds = array();

        foreach($clientUsers as $cu)
        {
            array_push($userIds, $cu->user_id);
        }

        $users = User::whereIn('id', $userIds)->get();

        return $users;
    }

    public function getClientsAssigned()
    {
        $userClients = DB::table('client_user')->select('client_id', 'user_id')->where('user_id', $this->id)->get();

        $clients = DB::table('clients')->select('id', 'first_name', 'last_name','company');

        $clientKeys = array();

        foreach($userClients as $cu)
        {
            array_push($clientKeys, $cu->client_id);
        }

        $clients = $clients->whereIn('id', $clientKeys)->get();

        return $clients;
    }

    public function isClientAssigned($clientId)
    {
        $clientUser = DB::table('client_user')->select('id')->where(['user_id'=>$this->id, 'client_id'=>$clientId])->get();

        return !$clientUser->isEmpty() ? TRUE : FALSE;
    }

    public function getAllUsers()
    {
        $users = User::orderBy('first_name', 'ASC')->get();

        return $users;
    }
}
