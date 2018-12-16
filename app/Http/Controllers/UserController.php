<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Client;
use Validator;
use App\User;

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

        if(!$user->hasPermissionTo('view user'))
        {
            return redirect()->route('Dashboard');
        }

        $users = User::getAccessibleUsers($user)
            ->orderBy('users.id', 'desc')
            ->search($request)
            ->paginate(10);

        return view('users.index', ['title'=>'Users', 'users'=>$users]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $user = auth()->user();

        if(!$user->hasPermissionTo('create user'))
        {
            return redirect()->route('Dashboard');
        }

        $roles = $user->hasRole('admin') ? 
            ['Admin', 'Client Admin', 'Client User'] :
            ['Client User'];

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

        if(!$user->hasPermissionTo('create user'))
        {
            return redirect()->route('Dashboard');
        }

        $raw = User::getStoreData($request);
        $errors = User::getStoreErrors($raw);

        if(!$errors->isEmpty())
        {
            $roles = $user->hasRole('admin') ? 
            ['Admin', 'Client Admin', 'Client User'] :
            ['Client User'];

            return view('users.create', [
                'title'=>'Create User',
                'errors'=>$errors->all(),
                'input' => $request->input(),
                'roles' => $roles,
            ]);
        }

        $data = User::cleanStoreData($raw);

        $createdUser = User::createUser($data, $user);        

        return redirect($createdUser->path);
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

        if(!$authUser->hasPermissionTo('view user'))
        {
            return redirect()->route('Dashboard');
        }

        if(!User::getAccessibleUsers($authUser)
            ->where('users.id', '=', $user->id)
            ->first())
        {
            return redirect()
                ->route('usersHome')
                ->with('flashError', 'You do not have access to that resource.');
        }
        
        return view('users.show', ['title'=>$user->username, 'user'=>$user]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(User $user)
    {
        $authUser = auth()->user();

        $roles = array();

        if($user->hasRole('admin'))
        {
            $roles = ['Admin', 'Client Admin', 'Client User'];
        }
        else
        {
            $roles = ['Client User'];
        }

        return view('users.edit', [
            'title' => 'Edit '.$user->username,
            'roles' => $roles,
            'user' => $user,
        ]);
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
        $authUser = auth()->user();

        if($authUser->hasPermissionTo('edit user'))
        {
            if(!empty(request('clients')))
            {
                $usersClients = array();
                foreach($authUser->getUserClients() as $userClient)
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
                // delete existing client user assignments
                DB::table('client_user')->where('user_id', $user->id)->delete();

                // update clients assigned to user
                $userClientIds = array();

                foreach($user->getUserClients() as $userClient)
                {
                    array_push($userClientIds, $userClient->client_id);
                }

                foreach(request('clients') as $clientId)
                {
                    if(! in_array($clientId, $userClientIds))
                    {
                        DB::table('client_user')->insert([
                            'user_id' => $user->id,
                            'client_id' => $clientId,
                        ]);
                    }
                }

                return redirect($user->path);
            }
            else
            {
                $roles = $authUser->hasRole('admin') ?
                    ['Admin', 'Client Admin', 'Client User'] :
                    ['Client User'];

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
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        $authUser = auth()->user();

        if($user)
            return redirect()->route('usersHome');
    }
}
