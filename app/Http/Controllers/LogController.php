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
    public function index()
    {
        $user = Auth()->user();

        if(!$user->hasPermissionTo('view log'))
        {
            return redirect()->route('Dashboard');
        }
        
        $logs = Log::orderBy('id', 'desc');

        if(!$user->hasRole('admin'))
        {
            $userClients = $user->getuserClients();

            $logsKeys = array();

            foreach($userClients as $userClient) 
            {
                $log = Log::distinct()->select('id')->where('client_id', $userClient->client_id)->first();

                array_push($logsKeys, $log->id);
            }
            
            $logs = $logs->whereIn('client_id', $logsKeys);
        }

        if(request('title') || request('desc') || request('body') || request('created_at') || request('updated_at'))
        {
            if(request('title'))
            {
                $logs = $logs->where('title', 'like', '%'.request('search').'%');
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
                $logs = $logs->whereDate('created_at', 'like', '%'.request('search').'%');
            }
            if(request('updated_at'))
            {
                $logs = $logs->whereDate('updated_at', 'like', '%'.request('search').'%');
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

        return view('logs.index', ['title'=>'Logs', 'logs'=>$logs]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if(!auth()->user()->hasPermissionTo('create log'))
            return redirect()->route('Dashboard');
        
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
            return redirect()->route('Dashboard');

        if(!$user->hasRole('admin'))
        {
            // does user have permission to edit this client?
            if(!$user->isClientAssigned(request('client_id')))
            {
                return view('logs.create', [
                    'title'=>'Create Log', 
                    'errors'=>['Oops, something went wrong.',
                    'input' => $request->input(),
                ]]);
            }
        }

        $validator = Validator::make($request->all(), [
            'client_id' => 'required|integer',
            'title' => 'required|max:191|min:3',
            'description'  => 'required|min:10',
            'body'  => 'required|min:10'
        ]);

        if(request('client_id') > 0 == false)
        {
            return view('logs.create', [
                'title'=>'Create Log', 
                'errors'=>['No client selected.'],
                'input' => $request->input(),
            ]);
        }

        if(!empty($validator->errors()->all())) {
            return view('logs.create', [
                'title'=>'Create Log',
                'errors'=>$validator->errors()->all(),
                'input' => $request->input(),
            ]);
        }

        Log::create([
            'client_id' => request('client_id'),
            'user_created' => $user->id,
            'title' => filter_var(request('title'), FILTER_SANITIZE_STRING),
            'description' => filter_var(request('description'), FILTER_SANITIZE_STRING),
            'body' => filter_var(request('body'), FILTER_SANITIZE_STRING),
            'notes' => !empty(request('notes')) ? filter_var(request('notes', FILTER_SANITIZE_STRING)) : NULL
        ]);

        return redirect()->route('logsHome');
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

        if(!$user->hasRole('admin'))
        {
            // check if log should be viewable by user
            if(!$user->isClientAssigned($log->client_id))
            {
                return redirect()->route('logsHome')->with('flashError', 'You do not have access to that resource.');
            }
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

        if(!$user->hasRole('admin'))
        {
            // check if log should be viewable by user
            if(!$user->isClientAssigned($log->client_id))
            {
                return redirect()->route('logsHome')->with('flashError', 'You do not have access to that resource.');
            }
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

        if(!$user->hasRole('admin'))
        {
            // check if log should be viewable by user
            if(!$user->isClientAssigned($log->client_id))
            {
                return redirect()->route('logsHome')->with('flashError', 'You do not have access to that resource.');
            }
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

        if(!$user->hasRole('admin'))
        {
            // check if log should be viewable by user
            if(!$user->isClientAssigned($log->client_id))
            {
                return redirect()->route('logsHome')->with('flashError', 'You do not have access to that resource.');
            }
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

        if(!$user->hasRole('admin'))
        {
            // check if log should be viewable by user
            if(!$user->isClientAssigned($log->client_id))
            {
                return redirect()->route('logsHome')->with('flashError', 'You do not have access to that resource.');
            }
        }

        if((int)request('delete') !== 1)
        {
            return redirect()->route('showLog', $log->id);
        }

        $log->delete();

        return redirect('/logs')->with('flashSuccess', 'Log successfully deleted.');

    }
}
