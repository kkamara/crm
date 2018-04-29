<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\User;
use Validator;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user = Auth()->user();

        if($user->hasPermissionTo('view user'))
        {
            $users = User::where('id', '!=', $user->id)->orderBy('id', 'desc');

            $clientUsers = $user->getClientUsers();

            $userIds = array();

            foreach($clientUsers as $u)
            {
                array_push($userIds, $u->id);
            }

            $users = $users->whereIn('id', $userIds)->search($request->all())->paginate(10);

            return view('users.index', ['title'=>'Users', 'users'=>$users]);
        }
        else
        {
            return redirect()->route('Dashboard');
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $user = auth()->user();

        $roles = array();

        if($user->hasRole('admin'))
        {
            $roles = ['Admin', 'Client Admin', 'Client User'];
        }
        else
        {
            $roles = ['Client User'];
        }

        return view('users.create', [
            'title' => 'Create User',
            'roles' => $roles,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $user = auth()->user();

        if($user->hasPermissionTo('create user'))
        {
            if(!empty(request('clients')))
            {
                $usersClients = array();
                foreach($user->getUserClients() as $userClient)
                {
                    array_push($usersClients, $userClient->client_id);
                }

                $validatedUserClients = array_intersect($usersClients, request('clients'));

                $hasAccessToClients = true;
                for($i=0;$i<count(request('clients'));$i++)
                {
                    if(!in_array(request('clients')[$i], $validatedUserClients))
                    {
                        $hasAccessToClients = false;
                    }
                }
            }

            // does user have permission to edit this client?
            if(! empty(request('clients')) && $hasAccessToClients)
            {
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

                if(! empty($role) && $permissionToAssignRole)
                {
                    $validator = Validator::make($request->all(), [
                        'first_name' => 'required|max:191|min:3',
                        'last_name' => 'required|max:191|min:3',
                        'email' => 'required|email|unique:users|max:191|min:3',
                        'password' => 'required|confirmed',
                    ]);

                    if(empty($validator->errors()->all()))
                    {
                        $username = str_slug(request('first_name').' '.request('last_name'), '-');

                        $existingUsernames = User::where('username', $username)->first();

                        if($existingUsernames !== null)
                        {
                            $param = str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ");
                            $username .= substr($param, 0, 4);
                        }

                        $createdUser = User::create([
                            'user_created' => $user->id,
                            'username' => $username,
                            'first_name' => filter_var(request('first_name'), FILTER_SANITIZE_STRING),
                            'last_name' => filter_var(request('last_name'), FILTER_SANITIZE_STRING),
                            'email' => filter_var(request('email'), FILTER_SANITIZE_STRING),
                            'password' => bcrypt(request('password')),
                        ]);

                        // assign user to clients
                        foreach(request('clients') as $clientId)
                        {
                            DB::table('client_user')->insert([
                                'user_id' => $createdUser->id,
                                'client_id' => $clientId,
                            ]);
                        }

                        // assign role
                        switch($role)
                        {
                            case 'admin':
                                $createdUser->assignRole('admin');
                            break;
                            case 'client admin':
                                $createdUser->assignRole('client_admin');
                            break;
                            case 'client':
                                $createdUser->assignRole('client_user');
                            break;
                        }

                        return redirect($createdUser->path());
                    }
                    else
                    {
                        $roles = array();

                        if($user->hasRole('admin'))
                        {
                            $roles = ['Admin', 'Client Admin', 'Client User'];
                        }
                        else
                        {
                            $roles = ['Client User'];
                        }

                        return view('users.create', [
                            'title'=>'Create User',
                            'errors'=>$validator->errors()->all(),
                            'input' => $request->input(),
                            'roles' => $roles,
                        ]);
                    }
                }
                else
                {
                    $roles = array();

                    if($user->hasRole('admin'))
                    {
                        $roles = ['Admin', 'Client Admin', 'Client User'];
                    }
                    else
                    {
                        $roles = ['Client User'];
                    }

                    return view('users.create', [
                        'title'=>'Create User',
                        'errors'=>['No Role selected'],
                        'input' => $request->input(),
                        'roles' => $roles,
                    ]);
                }
            }
            else
            {
                $roles = array();

                if($user->hasRole('admin'))
                {
                    $roles = ['Admin', 'Client Admin', 'Client User'];
                }
                else
                {
                    $roles = ['Client User'];
                }

                return view('users.create', [
                    'title'=>'Create User',
                    'errors'=>['No client selected.'],
                    'input' => $request->input(),
                    'roles' => $roles,
                ]);
            }
        }
        else
        {
            return redirect()->route('Dashboard');
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(User $user)
    {
        $authUser = Auth()->user();

        if($authUser->hasPermissionTo('view user'))
        {
            // check if client should be viewable by user
            $userClients = $authUser->getuserClients();

            $clientKeys = array();

            foreach($userClients as $userClient)
            {
                array_push($clientKeys, $userClient->client_id);
            }

            $clientUsers = DB::table('client_user')
                            ->select('user_id')
                            ->whereIn('client_id', $clientKeys)
                            ->where('user_id', '!=', $user->id)
                            ->distinct()
                            ->get();

            $usersKeys = array();

            foreach($clientUsers as $clientUser)
            {
                array_push($usersKeys, $clientUser->user_id);
            }

            $users = User::whereIn('id', $usersKeys)->where('id', '!=', $authUser->id)->distinct()->get();

            $clientUserKeys = array();

            foreach($users as $user)
            {
                array_push($clientUserKeys, $user->id);
            }

            $userExists = in_array($user->id, $clientUserKeys);

            if($userExists)
            {
                return view('users.show', ['title'=>$user->username, 'user'=>$user]);
            }
            else
            {
                return redirect()->route('usersHome')->with('flashError', 'You do not have access to that resource.');
            }
        }
        else
        {
            return redirect()->route('Dashboard');
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(User $user)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User $user)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        //
    }
}
