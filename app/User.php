<?php

namespace App;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

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
        // get client ids user is assigned to
        $userClients = DB::table('client_user')->select('client_id', 'user_id')->where('user_id', $this->id)->get();

        // store client ids
        $clientIds = array();

        foreach($userClients as $userClient)
        {
            array_push($clientIds, $userClient->client_id);
        }

        // query client users again to get all user ids except user currently logged in
        $clientUsers = DB::table('client_user')->where('user_id', '!=', $this->id)->get();

        // get unique users assigned to clients
        $userIds = array();

        foreach($clientUsers as $clientUser)
        {
            if(! in_array($clientUser->user_id, $userIds))
            {
                array_push($userIds, $clientUser->user_id);
            }
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

    public function getEditClientOptions($user)
    {
        $options = "";
        $clientAssignedIds = array();

        foreach($user->getClientsAssigned() as $userClient)
        {
            array_push($clientAssignedIds, $userClient->id);
        }

        foreach(auth()->user()->getClientsAssigned() as $client)
        {
            if(in_array($client->id, $clientAssignedIds))
            {
                $options .= "<option selected value='".$client->id."'>".$client->company."</option>";
            }
            else
            {
                $options .= "<option value='".$client->id."'>".$client->company."</option>";
            }
        }

        return $options;
    }

    public function removeAllRoles()
    {
        $this->removeRole('admin');
        $this->removeRole('client_admin');
        $this->removeRole('client_user');
    }
    
    /**
     * Update an instance of this resource.
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    public function updateUser($request)
    {
        $this->first_name = !empty($request->input('first_name')) ? trim(filter_var($request->input('first_name'), FILTER_SANITIZE_STRING)) : NULL;
        $this->last_name = !empty($request->input('last_name')) ? trim(filter_var($request->input('last_name'), FILTER_SANITIZE_STRING)) : NULL;
        $this->email = trim(filter_var($request->input('email'), FILTER_SANITIZE_EMAIL));
        $this->building_number = $request->input('building_number') !== NULL ? trim(filter_var($request->input('building_number'), FILTER_SANITIZE_STRING)) : NULL;
        $this->street_address = $request->input('street_name') !== NULL ? trim(filter_var($request->input('street_name'), FILTER_SANITIZE_STRING)) : NULL;
        $this->postcode = $request->input('postcode') !== NULL ? trim(filter_var($request->input('postcode'), FILTER_SANITIZE_STRING)) : NULL;
        $this->city = $request->input('city') !== NULL ? trim(filter_var($request->input('city'), FILTER_SANITIZE_STRING)) : NULL;
        $this->contact_number = $request->input('contact_number') !== NULL ? trim(filter_var($request->input('contact_number'), FILTER_SANITIZE_STRING)) : NULL;
        
        return $this->save() ? TRUE : FALSE;
    }
}
