<?php

namespace App;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\MessageBag;
use Carbon\Carbon;
use Validator;

class User extends Authenticatable
{
    /** This model uses the Notifiable trait for notifications. */
    use Notifiable;

    /** This model uses the HasRoles trait for a user being able to have a role. */
    use HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The attributes that should not be returned in outputs of the instance.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * Set a publicily accessible identifier to get the path for this unique instance.
     * 
     * @return  string
     */
    public function getPathAttribute()
    {
        return url('/').'/users/'.$this->id;
    }

    /**
     * Set a publicily accessible identifier to get the last login for this unique instance.
     * 
     * @return  string
     */
    public function getLastLoginAttribute()
    {
        if(empty($this->attributes['last_login']))
        {
            return '';
        }

        return Carbon::parse($this->attributes['last_login'])->diffForHumans();
    }

    /**
     * Set a publicily accessible identifier to get the name for this unique instance.
     * 
     * @return  string
     */
    public function getNameAttribute()
    {
        return $this->attributes['first_name'] . ' ' . $this->attributes['last_name'];
    }

    /**
     * Get clients that belong to an instance of this model.
     * 
     * @return  Illuminate\Support\Facades\DB
     */
    public function getUserClients()
    {
        $userClients = DB::table('client_user')
            ->select('client_id', 'user_id')
            ->where('user_id', $this->id)
            ->get();

        return $userClients;
    }

    /**
     * Gets a list of users that are also assigned to clients that are linked to an instance of this model.
     * 
     * @return Illuminate\Support\Collection
     */
    public function getClientUsers()
    {
        /** get client ids user is assigned to */
        $userClients = $this->getUserClients();

        /** store client ids */
        $clientIds = array();

        foreach($userClients as $userClient)
        {
            array_push($clientIds, $userClient->client_id);
        }

        /** query client users again to get all user ids except user currently logged in */
        $clientUsers = DB::table('client_user')->where('user_id', '!=', $this->id)->get();

        /** get unique users assigned to clients */
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

    /**
     * Returns a list of clients assigned to an instance of this model.
     * 
     * @return  Illuminate\Support\Collection
     */
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

    /**
     * Finds whether an instance of this model is assigned to a given client id.
     * 
     * @param  \App\Client  $clientId
     * @return bool
     */
    public function isClientAssigned($clientId)
    {
        $clientUser = DB::table('client_user')
            ->select('id')
            ->where(['user_id'=>$this->id, 'client_id'=>$clientId])->get();

        return !$clientUser->isEmpty() ? TRUE : FALSE;
    }

    /**
     * Adds onto a query parameters provided in request to search for items of this instance.
     * 
     * @param  \Illuminate\Database\Eloquent\Model  $query
     * @param  \Illuminate\Http\Request             $request
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function scopeSearch($query, $request)
    {
        if($request->has('search'))
        {   
            $searchParam = filter_var($request['search'], FILTER_SANITIZE_STRING);
            
            if($request->has('username') || $request->has('name') || $request->has('email') || $request->has('created_at') || $request->has('updated_at'))
            {
                if($request->has('username'))
                {
                    $query = $query->where('users.username', 'like', '%'.$searchParam.'%');
                }
                if($request->has('name'))
                {
                    $fullName = explode(' ', $searchParam);

                    if(sizeof($fullName) == 2)
                    {
                        $query = $query->where('users.first_name', 'like', '%'.$fullName[0].'%');
                        $query = $query->where('users.last_name', 'like', '%'.$fullName[1].'%');
                    }
                    else
                    {
                        $query = $query->where('users.first_name', 'like', '%'.$fullName[0].'%');
                    }
                }
                if($request->has('email'))
                {
                    $query = $query->where('users.email', 'like', '%'.$searchParam.'%');
                }
                if($request->has('created_at'))
                {
                    $query = $query->whereDate('users.created_at', 'like', '%'.$searchParam.'%');
                }
                if($request->has('updated_at'))
                {
                    $query = $query->whereDate('users.updated_at', 'like', '%'.$searchParam.'%');
                }
            }
            else
            {
                $query = $query->where('users.username', 'like', '%'.$searchParam.'%')
                                ->orWhere('users.first_name', 'like', '%'.$searchParam.'%')
                                ->orWhere('users.last_name', 'like', '%'.$searchParam.'%')
                                ->orWhere('users.email', 'like', '%'.$searchParam.'%')
                                ->orWhere('users.created_at', 'like', '%'.$searchParam.'%')
                                ->orWhere('users.updated_at', 'like', '%'.$searchParam.'%');
            }
        }

        return $query;
    }

    /**
     * Returns option values for html input select tags for edit users request.
     * 
     * @param  \App\User  $user
     * @return string
     */
    public function getEditClientOptions($user)
    {
        $options = "";
        $clientAssignedIds = [];

        $givenUserClients = Client::getAccessibleClients($user)->get();

        foreach($givenUserClients as $userClient)
        {
            $clientAssignedIds[] = $userClient->id;
        }

        $authUserClients = Client::getAccessibleClients(auth()->user())
            ->orderBy('clients.company', 'ASC')
            ->get();

        foreach($authUserClients as $client)
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
    
    /**
     * Update an instance of this resource.
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    /*
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
    */

    /**
     *  Get users available to a given user.
     *
     *  @param  \Illuminate\Database\Eloquent\Model $query
     *  @param  \App\User $user
     *  @return \Illuminate\Database\Eloquent\Model
     */
    public function scopeGetAccessibleUsers($query, $user)
    {
        return $query->select(
                'users.username', 'users.first_name', 'users.last_name', 'users.email', 
                'users.created_at', 'users.updated_at', 'users.id'
            )
            ->leftJoin('client_user', 'users.id', '=', 'client_user.user_id')
            ->where('users.id', '!=', $user->id)
            ->groupBy('users.id');
    }

    /**
     *  Get errors for store request of this resource.
     *
     *  @param  array  $data
     *  @param  \App\User $user
     *  @return \Illuminate\Support\MessageBag
     */
    public static function getStoreErrors($data, $user)
    {
        $errors = [];

        if(isset($data['client_ids']))
        {
            $hasAccessToClients = Client::hasAccessToClients($data['client_ids'], $user);
        }

        if(!isset($data['client_ids']) || !$hasAccessToClients)
        {
            $errors[] = 'No client selected.';
        }

        $role = strtolower(request('role'));
        $permissionToAssignRole = true;

        switch($role)
        {
            case 'admin':
            case 'client admin':
                if(!$user->hasRole('admin'))
                {
                    $permissionToAssignRole = false;
                }
            break;
            case 'client user':
            break;
            default:
                $permissionToAssignRole = false;
            break;
        }

        if(empty($data['role']) || !$permissionToAssignRole)
        {
            $errors[] = 'No Role selected';
        }

        $validator = Validator::make($data, [
            'first_name' => 'required|max:191|min:3',
            'last_name' => 'required|max:191|min:3',
            'email' => 'required|email|unique:users|max:191|min:3',
            'password' => 'required|confirmed',
        ]);

        return $validator->messages()->merge($errors);
    }

    /**
     *  Get store data
     *
     *  @param  \Illuminate\Http\Request  $request
     *  @return array
     */
    public static function getStoreData($request)
    {
        return [
            'first_name' => $request->input('first_name'),
            'last_name' => $request->input('last_name'),
            'email' => $request->input('email'),
            'password' => $request->input('password'),
            'password_confirmation' => $request->input('password_confirmation'),
            'client_ids' => $request->input('clients'),
            'role' => $request->input('role'),
        ];
    }

    /**
     *  Sanitize store data
     *
     *  @param  array $data
     *  @return array
     */
    public static function cleanStoreData($data)
    {
        $clientIds = array();
        for($i=0;$i<count($data['client_ids']);$i++)
        {
            $id = $data['client_ids'][$i];
            $clientIds[] = filter_var($id, FILTER_SANITIZE_NUMBER_INT);
        }

        return [
            'first_name' => filter_var($data['first_name'], FILTER_SANITIZE_STRING),
            'last_name' => filter_var($data['last_name'], FILTER_SANITIZE_STRING),
            'email' => filter_var($data['email'], FILTER_SANITIZE_EMAIL),
            'password' => $data['password'],
            'password_confirmation' => $data['password_confirmation'],
            'client_ids' => $clientIds,
            'role' => filter_var($data['role'], FILTER_SANITIZE_STRING),
        ];
    }

    /**
     *  Create db instance of this model.
     *
     *  @param  array $data
     *  @param  \App\User $user
     *  @return \App\User
     */
    public function createUser($data, $user)
    {
        $data['username'] = str_slug($data['first_name'].' '.$data['last_name'], '-');
        $existingUsernames = User::where('username', $data['username'])->first();

        if($existingUsernames !== null)
        {
            $param = str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ");
            $data['username'] .= substr($param, 0, 4);
        }

        $createdUser = User::create([
            'user_created' => $user->id,
            'username' => $data['username'],
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
        ]);

        // assign user to clients
        foreach($data['client_ids'] as $id)
        {
            DB::table('client_user')->insert([
                'user_id' => $createdUser->id,
                'client_id' => $id,
            ]);
        }

        // assign role
        switch($data['role'])
        {
            case 'admin':
                $createdUser->assignRole('admin');
            break;
            case 'client admin':
                $createdUser->assignRole('client_admin');
            break;
            case 'client user':
                $createdUser->assignRole('client_user');
            break;
        }

        return $createdUser;
    }

    /**
     *  Get errors for update request of this resource.
     *
     *  @param  array  $data
     *  @param  \App\User $user
     *  @return \Illuminate\Support\MessageBag
     */
    public static function getUpdateErrors($data, $user)
    {
        $errors = new MessageBag;

        if(isset($data['client_ids']))
        {
            $hasAccessToClients = Client::hasAccessToClients($data['client_ids'], $user);
        }

        if(!isset($data['client_ids']) || !$hasAccessToClients)
        {
            $errors->add('client_ids', 'No client selected.');
        }

        return $errors;
    }

    /**
     *  Get update data
     *
     *  @param  \Illuminate\Http\Request  $request
     *  @return array
     */
    public static function getUpdateData($request)
    {
        return [
            'client_ids' => $request->input('clients'),
        ];
    }

    /**
     *  Sanitize update data
     *
     *  @param  array $data
     *  @return array
     */
    public static function cleanUpdateData($data)
    {
        $clientIds = array();
        for($i=0;$i<count($data['client_ids']);$i++)
        {
            $id = $data['client_ids'][$i];
            $clientIds[] = filter_var($id, FILTER_SANITIZE_NUMBER_INT);
        }

        return [
            'client_ids' => $clientIds,
        ];
    }

    /**
     *  Create db instance of this model.
     *
     *  @param array $data
     */
    public function updateUser($data)
    {
        // assign user to clients
        foreach($data['client_ids'] as $id)
        {
            DB::table('client_user')->insert([
                'user_id' => $this->id,
                'client_id' => $id,
            ]);
        }
    }
}
