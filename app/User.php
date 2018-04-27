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

    public function path()
    {
        return url('/').'/users/'.$this->id;
    }

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

    public function getClientSpecificUsers()
    {
        $clients = DB::table('client_user')->where('user_id', $this->id)->where('user_id')->get();

        $clientKeys = array();

        foreach($clients as $client)
        {
            array_push($clientKeys, $client->client_id);
        }

        // get all user ids assigned to clients
        $clientUsers = DB::table('client_user')->whereIn('client_id', $clientKeys)->get();

        $userIds = array();

        foreach($clientUsers as $cu)
        {
            array_push($userIds, $cu->user_id);
        }

        $users = User::whereIn('id', $userIds)->where('id', '!=', $this->id)->get();

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

    public function scopeSearch($query, $request)
    {
        if(array_key_exists('username', $request) || array_key_exists('name', $request) || array_key_exists('email', $request) || array_key_exists('created_at', $request) || array_key_exists('updated_at', $request))
        {
            if(array_key_exists('search', $request))
            {
                $searchParam = filter_var($request['search'], FILTER_SANITIZE_STRING);

                if(array_key_exists('username', $request))
                {
                    $query = $query->where('username', 'like', '%'.$searchParam.'%');
                }
                if(array_key_exists('name', $request))
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
                if(array_key_exists('email', $request))
                {
                    $query = $query->where('email', 'like', '%'.$searchParam.'%');
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

                $query = $query->where('username', 'like', '%'.$searchParam.'%')
                                ->orWhere('first_name', 'like', '%'.$searchParam.'%')
                                ->orWhere('last_name', 'like', '%'.$searchParam.'%')
                                ->orWhere('email', 'like', '%'.$searchParam.'%')
                                ->orWhere('created_at', 'like', '%'.$searchParam.'%')
                                ->orWhere('updated_at', 'like', '%'.$searchParam.'%');
            }
        }

        return $query;
    }
}
