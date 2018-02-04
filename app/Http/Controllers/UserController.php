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

        $users = User::orderBy('id', 'desc');

        // if not admin then get users assigned
        // otherwise all users will be shown
        if(!$user->hasRole('admin'))
        {
            $userClients = $user->getuserClients();

            $usersKeys = array();
            
            foreach($userClients as $userClient) 
            {
                $user = User::where('id', $userClient->user_id)->first();

                array_push($usersKeys, $user->id);
            }
            
            $users = $users->whereIn('id', $usersKeys);
        }

        if(request('search'))
        {
            $searchParam = filter_var(request('search'), FILTER_SANITIZE_STRING);

            $users = $users->where('first_name', 'LIKE', '%'.$searchParam.'%')
                                ->orWhere('last_name', 'LIKE', '%'.$searchParam.'%')
                                ->orWhere('email', 'LIKE', '%'.$searchParam.'%')
                                ->orWhere('created_at', 'LIKE', '%'.$searchParam.'%')
                                ->orWhere('updated_at', 'LIKE', '%'.$searchParam.'%');
        }

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
            $authUser->getClientUsers();

            $userlist = $user->getClientSpecificUsers();

            $userExists = false;
            foreach($userlist as $ul)
            {
                if($user_param->id == $ul->id)
                {
                    $userExists = true;
                }
            }

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
