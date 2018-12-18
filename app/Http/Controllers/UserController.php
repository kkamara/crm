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
        $errors = User::getStoreErrors($raw, $user);

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

        $createdUser = (new User)->createUser($data, $user);        

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

        $roles = $user->hasRole('admin') ? 
            ['Admin', 'Client Admin', 'Client User'] :
            ['Client User'];

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

        if(!$authUser->hasPermissionTo('edit user'))
        {
            return redirect()->route('Dashboard');
        }

        $raw = User::getUpdateData($request);
        $errors = User::getUpdateErrors($raw, $authUser);

        if(!$errors->isEmpty())
        {
            $roles = $user->hasRole('admin') ? 
            ['Admin', 'Client Admin', 'Client User'] :
            ['Client User'];

            return view('users.edit', [
                'title'=>'Create User',
                'errors'=>$errors->all(),
                'input' => $request->input(),
                'roles' => $roles,
                'user' => $user,
            ]);
        }

        $data = User::cleanUpdateData($raw);

        // delete existing client user assignments
        DB::table('client_user')->where('user_id', $user->id)->delete();

        $user->updateUser($data, $user);        

        return redirect($user->path)->with('flashSuccess', 'User successfully updated.');;
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

        if(!$authUser->hasPermissionTo('delete user'))
            return redirect()->route('dashboard');

        if((int) request('delete') !== 1)
        {
            return redirect()->route('showUser', $user->slug);
        }

        DB::table('client_user')
            ->where('client_user.user_id', '=', $user->id)
            ->delete();

        $user->delete();    

        return redirect()->route('usersHome');
    }
}
