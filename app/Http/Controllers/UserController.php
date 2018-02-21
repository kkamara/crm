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
    public function index()
    {
        $user = Auth()->user();

        if(!$user->hasPermissionTo('view user'))
        {
            return redirect()->route('Dashboard');
        }

        $users = User::where('id', '!=', $user->id)->orderBy('id', 'desc');

        // if not admin then get users assigned
        // otherwise all users will be shown
        if(!$user->hasRole('admin'))
        {
            $userClients = $user->getuserClients();

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

            $users = $users->whereIn('id', $usersKeys);
        }

        // if(request('search'))
        // {
        //     $searchParam = filter_var(request('search'), FILTER_SANITIZE_STRING);

        //     $users = $users->where('username', 'LIKE', '%'.$searchParam.'%')
        //                         ->orwhere('first_name', 'LIKE', '%'.$searchParam.'%')
        //                         ->orWhere('last_name', 'LIKE', '%'.$searchParam.'%')
        //                         ->orWhere('email', 'LIKE', '%'.$searchParam.'%')
        //                         ->orWhere('created_at', 'LIKE', '%'.$searchParam.'%')
        //                         ->orWhere('updated_at', 'LIKE', '%'.$searchParam.'%');
        // }

        $users = $users->paginate(10);

        return view('users.index', ['title'=>'Users', 'users'=>$users]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
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

        if(!$authUser->hasRole('admin'))
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

            if(!$userExists)
            {
                return redirect()->route('usersHome')->with('flashError', 'You do not have access to that resource.');
            }
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
