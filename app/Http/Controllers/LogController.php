<?php

namespace App\Http\Controllers;

use App\Log;
use Illuminate\Http\Request;
use App\User;
use Illuminate\Support\Facades\DB;

class LogController extends Controller
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
        $logs = Log::orderBy('id', 'desc');

        $user = Auth()->user();

        if(!$user->hasPermissionTo('view log'))
        {
            return redirect()->route('Dashboard');
        }

        $clientUsers = $user->getClientUsers();

        $logsKeys = array();

        foreach($clientUsers as $clientUser) 
        {
            $log = Log::distinct()->select('id')->where('client_id', $clientUser->client_id)->get();

            array_push($logsKeys, $log[0]->id);
        }
        unset($log);
        
        $logs = Log::whereIn('id', $logsKeys)->OrderBy('id', 'desc');

        if(request('title') || request('desc') || request('body') || request('created_at') || request('updated_at'))
        {
            if(request('title'))
            {
                $logs = $logs->where('title', 'like', '%'.request('search').'%');
                            // ->where('description', 'like', '%'.$searchParam.'%')
                            // ->where('body', 'like', '%'.$searchParam.'%')
                            // ->where('created_at', 'like', '%'.$searchParam.'%')
                            // ->where('updated_at', 'like', '%'.$searchParam.'%');
            }
            if(request('desc'))
            {
                $logs = $logs->where('description', 'like', '%'.request('search').'%');
            }
            if(request('body'))
            {
                $logs = $logs->where('body', 'like', '%'.request('search').'%');
            }
            if(request('created_at'))
            {
                $logs = $logs->where('created_at', 'like', '%'.request('search').'%');
            }
            if(request('updated_at'))
            {
                $logs = $logs->where('updated_at', 'like', '%'.request('search').'%');
            }
        }
        elseif(request('search'))
        {
            $logs = $logs->where('title', 'like', '%'.request('search').'%')
                         ->orWhere('description', 'like', '%'.request('search').'%')
                         ->orWhere('body', 'like', '%'.request('search').'%')
                         ->orWhere('created_at', 'like', '%'.request('search').'%')
                         ->orWhere('updated_at', 'like', '%'.request('search').'%');
        }

        $logs = $logs->paginate(10);

        return view('logs.index', ['title'=>'View Logs', 'logs'=>$logs]);
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
     * @param  \App\Log  $log
     * @return \Illuminate\Http\Response
     */
    public function show(Log $log)
    {
        $user = Auth()->user();
        
        if(!$user->hasPermissionTo('view log'))
        {
            return redirect()->route('Dashboard');
        }

        // check if log should be viewable by user
        $clientUsers = $user->getClientUsers();
        $allowed = false;

        foreach($clientUsers as $clientUser)
        {
            if($clientUser->client_id == $log->id)
            {
                $allowed = true;
            }
        }

        if(!$allowed)
        {
            return redirect()->route('logsHome')->with('flashError', 'You do not have access to that resource.');
        }

        return view('logs.show', ['title'=>$log->title, 'log'=>$log]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Log  $log
     * @return \Illuminate\Http\Response
     */
    public function edit(Log $log)
    {
        $user = Auth()->user();
        
        if(!$user->hasPermissionTo('edit log'))
        {
            return redirect()->route('Dashboard');
        }

        // check if log should be viewable by user
        $clientUsers = $user->getClientUsers();
        $allowed = false;

        foreach($clientUsers as $clientUser)
        {
            if($clientUser->client_id == $log->id)
            {
                $allowed = true;
            }
        }

        if(!$allowed)
        {
            return redirect()->route('logsHome')->with('flashError', 'You do not have access to that resource.');
        }

        return view('logs.edit')
                ->withTitle('Edit '.$log->title)
                ->withLog($log);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Log  $log
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Log $log)
    {
        $user = Auth()->user();
        
        if(!$user->hasPermissionTo('edit log'))
        {
            return redirect()->route('Dashboard');
        }

        // check if log should be viewable by user
        $clientUsers = $user->getClientUsers();
        $allowed = false;

        foreach($clientUsers as $clientUser)
        {
            if($clientUser->client_id == $log->id)
            {
                $allowed = true;
            }
        }

        if(!$allowed)
        {
            return redirect()->route('logsHome')->with('flashError', 'You do not have access to that resource.');
        }

        $errors = $log->validationErrors($request);

        if($errors)
        {
            return view('logs.edit', [
                'title' => 'Edit '.$log->title,
                'log' => $log,
                'errors' => $errors,
            ]);
        }

        $data = $log->parseData($request);
        
        $update = $log->updateLog($data);

        if($update === TRUE)
        {
            return redirect('/logs/'.$log->id)->with('flashSuccess', 'Log successfully updated.');
        }
        else
        {
            return redirect('/logs/'.$log->id)->with('flashError', 'We encountered an error updating the log.');
        }
    }

    public function delete(Log $log)
    {

        $user = Auth()->user();
        
        if(!$user->hasPermissionTo('delete log'))
        {
            return redirect()->route('Dashboard');
        }

        // check if log should be viewable by user
        $clientUsers = $user->getClientUsers();
        $allowed = false;

        foreach($clientUsers as $clientUser)
        {
            if($clientUser->client_id == $log->id)
            {
                $allowed = true;
            }
        }

        if(!$allowed)
        {
            return redirect()->route('logsHome')->with('flashError', 'You do not have access to that resource.');
        }

        return view('logs.delete', [
            'title'=>'Delete '.$log->title,
            'log'  => $log
        ]);
    }
    
    public function destroy(Log $log)
    {
        $user = Auth()->user();
        
        if(!$user->hasPermissionTo('delete log'))
        {
            return redirect()->route('Dashboard');
        }

        // check if log should be viewable by user
        $clientUsers = $user->getClientUsers();
        $allowed = false;

        foreach($clientUsers as $clientUser)
        {
            if($clientUser->client_id == $log->id)
            {
                $allowed = true;
            }
        }

        if(!$allowed)
        {
            return redirect()->route('logsHome')->with('flashError', 'You do not have access to that resource.');
        }

        if((int)request('delete') !== 1)
        {
            return redirect()->route('showLog', $log->id);
        }


        if($log->delete())
        {
            return redirect('/logs')->with('flashSuccess', 'Log successfully deleted.');
        }
        else
        {
            return redirect('/logs')->with('flashError', 'We encountered an error deleting the log.');
        }

    }
}
