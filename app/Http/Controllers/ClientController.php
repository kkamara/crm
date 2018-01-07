<?php

namespace App\Http\Controllers;

use App\Client;
use Illuminate\Http\Request;

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
        $clients = Client::orderBy('id', 'desc');

        $user = Auth()->user();

        if(!$user->hasPermissionTo('view client'))
        {
            return redirect()->route('Dashboard');
        }

        $clientUsers = $user->getClientUsers();

        $clientsKeys = array();
// dd($clientsKeys);
        foreach($clientUsers as $clientUser) 
        {
            $client = Client::where('id', $clientUser->client_id)->get();

            array_push($clientsKeys, $client[0]->id);
        }
        unset($client);
        // dd($clientsKeys);
        
        $clients = Client::whereIn('id', $clientsKeys)->OrderBy('id', 'desc');

        if(request('search'))
        {
            $clients = $clients->where('first_name', 'like', '%'.request('search').'%')
                                ->orWhere('last_name', 'like', '%'.request('search').'%')
                                ->orWhere('company', 'like', '%'.request('search').'%')
                                ->orWhere('email', 'like', '%'.request('search').'%')
                                ->orWhere('contact_number', 'like', '%'.request('search').'%')
                                ->orWhere('building_number', 'like', '%'.request('search').'%')
                                ->orWhere('city', 'like', '%'.request('search').'%')
                                ->orWhere('postcode', 'like', '%'.request('search').'%')
                                ->orWhere('created_at', 'like', '%'.request('search').'%')
                                ->orWhere('updated_at', 'like', '%'.request('search').'%');
        }

        $clients = $clients->paginate(10);

        return view('clients.index', ['title'=>'Clients', 'clients'=>$clients]);
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
     * @param  \App\Client  $client
     * @return \Illuminate\Http\Response
     */
    public function show(Client $client)
    {
        $user = Auth()->user();
        
        if(!$user->hasPermissionTo('view client'))
        {
            return redirect()->route('Dashboard');
        }

        // check if client should be viewable by user
        $clientUsers = $user->getClientUsers();
        $allowed = false;

        foreach($clientUsers as $clientUser)
        {
            if($clientUser->client_id == $client->id && $clientUser->user_id == $user->id)
            {
                $allowed = true;
            }
        }

        if(!$allowed)
        {
            return redirect()->route('clientsHome')->with('flashError', 'You do not have access to that resource.');
        }

        return view('clients.show', ['title'=>$client->company, 'client'=>$client]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Client  $client
     * @return \Illuminate\Http\Response
     */
    public function edit(Client $client)
    {
        //
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
        //
    }

    public function delete(Client $client)
    {
        
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Client  $client
     * @return \Illuminate\Http\Response
     */
    public function destroy(Client $client)
    {
        //
    }
}
