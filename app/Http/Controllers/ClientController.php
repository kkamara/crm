<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Client;
use Validator;

class ClientController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = Auth()->user();

        if($user->hasPermissionTo('view client'))
        {
            $clients = Client::orderBy('id', 'desc');

            $userClients = $user->getuserClients();

            $clientsKeys = array();

            foreach($userClients as $userClient)
            {
                $client = Client::where('id', $userClient->client_id)->get();

                if(empty($client)) break;

                foreach($client as $c)
                {
                    array_push($clientsKeys, $c->id);
                }
            }

            $clients = $clients->whereIn('id', $clientsKeys);

            $clients = $clients->paginate(10);

            return view('clients.index', ['title'=>'Clients', 'clients'=>$clients]);
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

        if($user->hasPermissionTo('create client'))
        {
            if($user->hasRole('admin'))
            {
                $userList = $user->getAllUsers();
            }
            // if role = client admin show users for clients user is assigned to
            elseif($user->hasRole('client_admin'))
            {
                $userList = $user->getClientUsers();
            }
            else
            {
                $userList = array($user);
            }
            // if role = client user or no role show only themselves
            return view('clients.create', [
                'title'=>'Create Client',
                'users' => $userList
            ]);
        }
        else
        {
            return redirect()->route('Dashboard');
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if(auth()->user()->hasPermissionTo('create client'))
        {
            $validator = Validator::make($request->all(), [
                'first_name' => 'max:191',
                'last_name' => 'max:191',
                'company' => 'required|max:191|min:3|unique:clients',
                'email' => 'required|email|max:191',
                'building_number' => 'required|max:191',
                'street_name' => 'required|max:191',
                'city' => 'required|max:191',
                'postcode' => 'required|max:191',
                // optional
                'contact_number' => 'max:191',
                'image' => 'image',
                'client_users' => 'required|array',
                'client_users.*' => 'required|integer',
            ]);

            $user = auth()->user();

            if(empty($validator->errors()->all()))
            {
                // check if user have access to users provided in client_users field
                $hasAccessToClientUser = true;

                if($user->hasRole('admin'))
                {
                    //
                }
                else
                {
                    if($user->hasRole('client_admin'))
                    {
                        $userList = $user->getClientUsers();

                        $userIds = array();

                        foreach($userList as $u)
                        {
                            array_push($userIds, $u->id);
                        }

                        // check if authenticated user has permission to assign user to client

                        $validatedClientUsers = array_intersect($userIds, request('client_users'));

                        $hasAccessToClientUser = true;

                        for($i=0;$i<count(request('client_users'));$i++)
                        {
                            if(!in_array(request('client_users')[$i], $validatedClientUsers))
                            {
                                $hasAccessToClientUser = false;
                            }
                        }
                    }
                    else
                    {
                        if(request('client_users')[0] != $user->id || count(request('client_users')))
                        {
                            $hasAccessToClientUser = false;
                        }
                    }
                }

                if($hasAccessToClientUser)
                {
                    // get image name
                    if(Input::hasFile('image'))
                    {
                        $file = Input::file('image');
                        $imageName = $file->getClientOriginalName();
                    }

                    $client = Client::create([
                        'user_created' => auth()->user()->id,
                        'first_name' => !empty(request('first_name')) ? filter_var(request('first_name'), FILTER_SANITIZE_STRING) : NULL,
                        'last_name' => !empty(request('last_name')) ? filter_var(request('last_name'), FILTER_SANITIZE_STRING) : NULL,
                        'company' => filter_var(request('company'), FILTER_SANITIZE_STRING),
                        'email' => filter_var(request('email'), FILTER_SANITIZE_EMAIL),
                        'building_number' => filter_var(request('building_number'), FILTER_SANITIZE_STRING),
                        'street_name' => filter_var(request('street_name'), FILTER_SANITIZE_STRING),
                        'postcode' => filter_var(request('postcode'), FILTER_SANITIZE_STRING),
                        'city' => filter_var(request('city'), FILTER_SANITIZE_STRING),
                        'contact_number' => !empty(request('contact_number')) ? filter_var(request('contact_number'), FILTER_SANITIZE_STRING) : NULL,
                        'image' => isset($imageName) ? $imageName : NULL,
                    ]);

                    // store image file if provided
                    if(isset($file) && isset($imageName))
                    {
                        $file->move(public_path('uploads/clients/'.$client->id), $imageName);
                    }

                    // assign users to clients
                    $clientUsers = array();

                    for($i=0;$i<count(request('client_users'));$i++)
                    {
                        $array = array(
                            'user_id' => request('client_users')[$i],
                            'client_id' => $client->id,
                        );

                        array_push($clientUsers, $array);
                    }

                    DB::table('client_user')->insert($clientUsers);

                    return redirect()->route('clientsHome');
                }
                else
                {
                    if(!$user->hasPermissionTo('create client')) return redirect()->route('Dashboard');

                    // if role = admin show all users
                    if($user->hasRole('admin'))
                    {
                        $userList = $user->getAllUsers();
                    }
                    // if role = client admin show users for clients user is assigned to
                    elseif($user->hasRole('client_admin'))
                    {
                        $userList = $user->getClientUsers();
                    }
                    // if role = client user or no role show only themselves
                    else
                    {
                        $userList = array($user);
                    }

                    return view('clients.create', [
                        'title'=>'Create Client',
                        'users' => $userList,
                        'input' => $request->input(),
                    ]);
                }
            }
            else
            {
                // if role = admin show all users
                if($user->hasRole('admin'))
                {
                    $userList = $user->getAllUsers();
                }
                // if role = client admin show users for clients user is assigned to
                elseif($user->hasRole('client_admin'))
                {
                    $userList = $user->getClientUsers();
                }
                // if role = client user or no role show only themselves
                else
                {
                    $userList = array($user);
                }

                return view('clients.create', [
                    'title'=>'Create Client',
                    'errors'=>$validator->errors()->all(),
                    'input' => $request->input(),
                    'users' => $userList,
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
     * @param  \App\Client  $client
     * @return \Illuminate\Http\Response
     */
    public function show(Client $client)
    {
        $user = Auth()->user();

        if($user->hasPermissionTo('view client'))
        {
            if($user->isClientAssigned($client->id))
            {
                return view('clients.show', ['title'=>$client->company, 'client'=>$client]);
            }
            else
            {
                return redirect()->route('clientsHome')->with('flashError', 'You do not have access to that resource.');
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
     * @param  \App\Client  $client
     * @return \Illuminate\Http\Response
     */
    public function edit(Client $client)
    {
        $user = Auth()->user();

        if($user->hasPermissionTo('edit client'))
        {
            if($user->isClientAssigned($client->id))
            {
                return view('clients.edit', [
                    'title'=>'Edit '.$client->company,
                    'client' => $client,
                ]);
            }
            else
            {
                return redirect()->route('clientsHome')->with('flashError', 'You do not have access to that resource.');
            }
        }
        else
        {
            return redirect()->route('Dashboard');
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Client  $client
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Client $client)
    {
        $user = Auth()->user();

        if($user->hasPermissionTo('edit client'))
        {
            if($user->isClientAssigned($client->id))
            {
                $validator = Validator::make($request->all(), [
                    'first_name' => 'max:191',
                    'last_name' => 'max:191',
                    'email' => 'required|email|max:191',
                    'building_number' => 'required|max:191',
                    'street_name' => 'required|max:191',
                    'city' => 'required|max:191',
                    'postcode' => 'required|max:191',
                    // optional
                    'contact_number' => 'max:191',
                    'image' => 'image',
                ]);

                if(empty($validator->errors()->all()))
                {
                    $imageName = null;

                    // get image name
                    if(Input::hasFile('image'))
                    {
                        $file = Input::file('image');
                        $imageName = $file->getClientOriginalName();

                        $client->updateClient($imageName);
                    }

                    // store image file if provided
                    if(isset($file) && isset($imageName))
                    {
                        $file->move(public_path('uploads/clients/'.$client->id), $imageName);
                    }

                    return redirect()->route('clientsHome');
                }
                else
                {
                    return view('clients.edit', [
                        'title' => 'Edit '.$client->company,
                        'errors' => $validator->errors()->all(),
                        'input' => $request->input(),
                        'client' => $client,
                    ]);
                }
            }
            else
            {
                return redirect()->route('clientsHome')->with('flashError', 'You do not have access to that resource.');
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
     * @param  \App\Client  $client
     * @return \Illuminate\Http\Response
     */
    public function destroy(Client $client)
    {
        $user = Auth()->user();

        if($user->hasPermissionTo('delete client'))
        {
            // check if client should be viewable by user
            if($user->isClientAssigned($client->id))
            {
                if(request('delete') == 1)
                {
                    $client->delete();

                    return redirect('/clients')->with('flashSuccess', 'Client successfully deleted.');
                }
                else
                {
                    return redirect()->route('showClient', $client->id);
                }
            }
            else
            {
                return redirect()->route('clientsHome')->with('flashError', 'You do not have access to that resource.');
            }
        }
        else
        {
            return redirect()->route('Dashboard');
        }
    }
}
