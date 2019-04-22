<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Validator;
use App\User;
use App\Log;

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
    public function index(Request $request)
    {
        $user = Auth()->user();

        if(!$user->hasPermissionTo('view log'))
        {
            return redirect()->route('Dashboard');
        }

        $logs = Log::getAccessibleLogs($user)
            ->orderBy('logs.id', 'desc')
            ->search($request)
            ->paginate(10); 

        return view('logs.index', ['title'=>'Logs', 'logs'=>$logs]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $user = Auth()->user();

        if(!$user->hasPermissionTo('create log'))
        {
            return redirect()->route('Dashboard');
        }

        return view('logs.create', ['title'=>'Create Log']);
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

        if(!$user->hasPermissionTo('create log'))
        {
            return redirect()->route('Dashboard');
        }

        $raw = Log::getStoreData($request, $user);
        $errors = Log::getStoreErrors($raw, $user);

        if(!$errors->isEmpty())
        {
            return view('logs.create', [
                'title'=>'Create Log',
                'errors'=>$errors->all(),
                'input' => $request->input(),
            ]);
        }

        $data = Log::cleanStoreData($raw);

        $log = Log::createLog($data);

        return redirect($log->path);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Log  $log
     * @return \Illuminate\Http\Response
     */
    public function show($logSlug)
    {
        $log = Log::where('slug', $logSlug)->first();

        if($log === null)
        {
            return redirect()->route('logsHome')->with('flashError', 'Resource not found.');
        }

        $user = Auth()->user();

        if(!$user->hasPermissionTo('view log'))
        {
            return redirect()->route('Dashboard');
        }

        // check if log should be viewable by user
        if(!Log::getAccessibleLogs($user)
            ->where('client_user.client_id', $log->client_id)
            ->first())
        {
            return redirect()
                ->route('logsHome')
                ->with('flashError', 'You do not have access to that resource.');
        }

        return view('logs.show', ['title'=>$log->title, 'log'=>$log]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Log  $log
     * @return \Illuminate\Http\Response
     */
    public function edit($logSlug)
    {
        $log = Log::where('slug', $logSlug)->first();

        if($log === null)
        {
            return redirect()->route('clientsHome')->with('flashError', 'Resource not found.');
        }

        $user = Auth()->user();

        if(!$user->hasPermissionTo('edit log'))
        {
            return redirect()->route('Dashboard');
        }
        
        // check if log should be viewable by user
        if(!Log::getAccessibleLogs($user)
            ->where('client_user.client_id', $log->client_id)
            ->first())
        {
            return redirect()
                ->route('logsHome')
                ->with('flashError', 'You do not have access to that resource.');
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
    public function update(Request $request, $logSlug)
    {
        $log = Log::where('slug', $logSlug)->first();

        if($log === null)
        {
            return redirect()->route('clientsHome')->with('flashError', 'Resource not found.');
        }

        $user = Auth()->user();

        if(!$user->hasPermissionTo('edit log'))
        {
            return redirect()->route('Dashboard');
        }

        // check if log should be viewable by user
        if(!Log::getAccessibleLogs($user)
            ->where('client_user.client_id', $log->client_id)
            ->first())
        {
            return redirect()->route('logsHome')->with('flashError', 'You do not have access to that resource.');
        }

        $raw = Log::getUpdateData($request);
        $errors = Log::getUpdateErrors($raw);

        if(!$errors->isEmpty())
        {
            return view('logs.edit', [
                'title' => 'Edit '.$log->title,
                'log' => $log,
                'errors' => $errors->all(),
            ]);
        }

        $data = Log::cleanUpdateData($raw);

        $log = $log->updateLog($data, $user);

        return redirect($log->path)->with('flashSuccess', 'Log successfully updated.');
    }

    public function delete($logSlug)
    {
        $log = Log::where('slug', $logSlug)->first();

        if($log === null)
        {
            return redirect()->route('clientsHome')->with('flashError', 'Resource not found.');
        }

        $user = Auth()->user();

        if(!$user->hasPermissionTo('delete log'))
        {
            return redirect()->route('Dashboard');
        }

        // check if log should be viewable by user
        if(!Log::getAccessibleLogs($user)
            ->where('client_user.client_id', $log->client_id)
            ->first())
        {
            return redirect()->route('logsHome')->with('flashError', 'You do not have access to that resource.');
        }

        return view('logs.delete', [
            'title'=>'Delete '.$log->title,
            'log'  => $log
        ]);
    }

    public function destroy($logSlug)
    {
        $log = Log::where('slug', $logSlug)->first();
        $user = Auth()->user();

        if(!$user->hasPermissionTo('delete log'))
        {
            return redirect()->route('Dashboard');
        }

        if((int) request('delete') !== 1)
        {
            return redirect()->route('showLog', $log->slug);
        }

        // check if log should be viewable by user
        if(!Log::getAccessibleLogs($user)
            ->where('client_user.client_id', $log->client_id)
            ->first())
        {
            return redirect()
                ->route('logsHome')
                ->with('flashError', 'You do not have access to that resource.');
        }

        $log->delete();

        return redirect()->route('logsHome')->with('flashSuccess', 'Log successfully deleted.');        
    }
}
