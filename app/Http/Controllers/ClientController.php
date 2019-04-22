<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Client;
use Validator;

class ClientController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user = Auth()->user();

        if(!$user->hasPermissionTo('view client'))
        {
            return redirect()->route('Dashboard');
        }

        $clients = Client::getAccessibleClients($user)
            ->orderBy('clients.id', 'DESC')
            ->search($request)
            ->paginate(10);

        return view('clients.index', ['title'=>'Clients', 'clients'=>$clients]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $user = auth()->user();

        if(!$user->hasPermissionTo('create client'))
        {
            return redirect()->route('Dashboard');
        }

        $users = $user->getAccessibleUsers($user)
            ->orderBy('first_name', 'ASC')
            ->get();

        // if role = client user or no role show only themselves
        return view('clients.create', [
            'title'=>'Create Client',
            'users' => $users
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

        if(!$user->hasPermissionTo('create client'))
        {
            return redirect()->route('Dashboard');
        }

        $raw = Client::getStoreData($request);
        $errors = Client::getStoreErrors($raw, $user);

        // handle errors
        if(!$errors->isEmpty())
        {
            $users = $user->getClientUsers();

            return view('clients.create', [
                'title'=>'Create Client',
                'errors'=>$errors->all(),
                'input' => $request->input(),
                'users' => $users,
            ]);
        }

        $data = Client::cleanStoreData($raw);

        if(!$errors->isEmpty())
        {
            $users = $user->getClientUsers();

            return view('clients.create', [
                'title'=>'Create Client',
                'errors'=>$errors->all(),
                'input' => $data,
                'users' => $users,
            ]);
        }

        $client = (new Client)->createClient($data, $user);

        return redirect($client->path);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Client  $client
     * @return \Illuminate\Http\Response
     */
    public function show($clientSlug)
    {
        $client = Client::where('slug', $clientSlug)->first();

        if($client === null)
        {
            return redirect()->route('clientsHome')->with('flashError', 'Resource not found.');
        }
            
        $user = Auth()->user();

        if(!$user->hasPermissionTo('view client'))
        {
            return redirect()->route('Dashboard');
        }

        if(!Client::getAccessibleClients($user)
            ->where('client_id', '=', $client->id)
            ->first())
        {
            return redirect()
                ->route('clientsHome')
                ->with('flashError', 'You do not have access to that resource.');
        }

        return view('clients.show', ['title'=>$client->company, 'client'=>$client]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Client  $client
     * @return \Illuminate\Http\Response
     */
    public function edit($clientSlug)
    {
        $client = Client::where('slug', $clientSlug)->first();

        if($client === null)
        {
            return redirect()->route('clientsHome')->with('flashError', 'Resource not found.');
        }

        $user = Auth()->user();

        if(!$user->hasPermissionTo('edit client'))
        {
            return redirect()->route('Dashboard');
        }

        if(!Client::getAccessibleClients($user)
            ->where('client_id', '=', $client->id)
            ->first())
        {
            return redirect()
                ->route('clientsHome')
                ->with('flashError', 'You do not have access to that resource.');
        }

        return view('clients.edit', [
            'title'=>'Edit '.$client->company,
            'client' => $client,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Client  $client
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $clientSlug)
    {
        $client = Client::where('slug', $clientSlug)->first();

        if($client === null)
        {
            return redirect()->route('clientsHome')->with('flashError', 'Resource not found.');
        }

        $user = Auth()->user();

        if(!$user->hasPermissionTo('edit client'))
        {
            return redirect()->route('Dashboard');
        }

        if(!Client::getAccessibleClients($user)
            ->where('client_id', '=', $client->id)
            ->first())
        {
            return redirect()
                ->route('clientsHome')
                ->with('flashError', 'You do not have access to that resource.');
        }

        $raw = Client::getUpdateData($request);
        $errors = Client::getUpdateErrors($raw);

        // handle errors
        if(!$errors->isEmpty())
        {
            return view('clients.edit', [
                'title' => 'Edit '.$client->company,
                'errors' => $errors->all(),
                'input' => $request->input(),
                'client' => $client,
            ]);
        }

        $data = Client::cleanUpdateData($raw);

        $client = $client->updateClient($data, $user);

        return redirect()->route('showClient', $client->slug);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Client  $client
     * @return \Illuminate\Http\Response
     */
    public function destroy($clientSlug)
    {
        $client = Client::where('slug', $clientSlug)->first();

        if($client === null)
        {
            return redirect()->route('clientsHome')->with('flashError', 'Resource not found.');
        }

        $user = Auth()->user();

        if(!$user->hasPermissionTo('delete client'))
        {
            return redirect()->route('Dashboard');
        }

        // check if client should be viewable by user
        if(!Client::getAccessibleClients($user)
            ->where('client_id', '=', $client->id)
            ->first())
        {
            return redirect()
                ->route('clientsHome')
                ->with('flashError', 'You do not have access to that resource.');
        }

        if((int) request('delete') !== 1)
        {
            return redirect()->route('showClient', $client->slug);
        }

        $client->delete();

        return redirect('/clients')->with('flashSuccess', 'Client successfully deleted.');  
    }
}
