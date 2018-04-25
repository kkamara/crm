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

        if($user->hasPermissionTo('view log'))
        {
            // if not admin then get logs assigned
            // otherwise all logs will be shown
            if($user->hasRole('admin'))
            {
                $logs = Log::orderBy('id', 'desc');
            }
            else
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
                $searchParam = filter_var(request('search'), FILTER_SANITIZE_STRING);

                if(request('title'))
                {
                    $logs = $logs->where('title', 'like', '%'.$searchParam.'%');
                }
                if(request('desc'))
                {
                    $logs = $logs->where('description', 'like', '%'.$searchParam.'%');
                }
                if(request('body'))
                {
                    $logs = $logs->where('body', 'like', '%'.$searchParam.'%');
                }
                if(request('created_at'))
                {
                    $logs = $logs->whereDate('created_at', 'like', '%'.$searchParam.'%');
                }
                if(request('updated_at'))
                {
                    $logs = $logs->whereDate('updated_at', 'like', '%'.$searchParam.'%');
                }
            }
            else
            {
                if(request('search'))
                {
                    $searchParam = filter_var(request('search'), FILTER_SANITIZE_STRING);

                    $logs = $logs->where('title', 'like', '%'.$searchParam.'%')
                                    ->orWhere('description', 'like', '%'.$searchParam.'%')
                                    ->orWhere('body', 'like', '%'.$searchParam.'%')
                                    ->orWhere('created_at', 'like', '%'.$searchParam.'%')
                                    ->orWhere('updated_at', 'like', '%'.$searchParam.'%');
                }
            }

            $logs = $logs->paginate(10);

            return view('logs.index', ['title'=>'Logs', 'logs'=>$logs]);
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
        if(auth()->user()->hasPermissionTo('create log'))
        {
            return view('logs.create', ['title'=>'Create Log']);
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
        $user = auth()->user();

        if($user->hasPermissionTo('create log'))
        {
            // does user have permission to edit this client?
            if($user->isClientAssigned(request('client_id')))
            {
                $validator = Validator::make($request->all(), [
                    'client_id' => 'required|integer',
                    'title' => 'required|max:191|min:3',
                    'description'  => 'required|min:10',
                    'body'  => 'required|min:10'
                ]);

                if(request('client_id') > 0)
                {
                    if(empty($validator->errors()->all()))
                    {
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
                    else
                    {
                        return view('logs.create', [
                            'title'=>'Create Log',
                            'errors'=>$validator->errors()->all(),
                            'input' => $request->input(),
                        ]);
                    }
                }
                else
                {
                    return view('logs.create', [
                        'title'=>'Create Log',
                        'errors'=>['Oops, something went wrong.',
                        'input' => $request->input(),
                    ]]);
                }
            }
            else
            {
                return view('logs.create', [
                    'title'=>'Create Log',
                    'errors'=>['No client selected.'],
                    'input' => $request->input(),
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
     * @param  \App\Log  $log
     * @return \Illuminate\Http\Response
     */
    public function show(Log $log)
    {
        $user = Auth()->user();

        if($user->hasPermissionTo('view log'))
        {
            // check if log should be viewable by user
            if($user->isClientAssigned($log->client_id))
            {
                return view('logs.show', ['title'=>$log->title, 'log'=>$log]);
            }
            else
            {
                return redirect()->route('logsHome')->with('flashError', 'You do not have access to that resource.');
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
     * @param  \App\Log  $log
     * @return \Illuminate\Http\Response
     */
    public function edit(Log $log)
    {
        $user = Auth()->user();

        if($user->hasPermissionTo('edit log'))
        {
            // check if log should be viewable by user
            if($user->isClientAssigned($log->client_id))
            {
                return view('logs.edit')
                    ->withTitle('Edit '.$log->title)
                    ->withLog($log);
            }
            else
            {
                return redirect()->route('logsHome')->with('flashError', 'You do not have access to that resource.');
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
     * @param  \App\Log  $log
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Log $log)
    {
        $user = Auth()->user();

        if($user->hasPermissionTo('edit log'))
        {
            // check if log should be viewable by user
            if($user->isClientAssigned($log->client_id))
            {
                $errors = $log->validationErrors($request);

                if(empty($errors))
                {
                    $data = $log->parseData($request);

                    $update = $log->updateLog($data);

                    return redirect('/logs/'.$log->id)->with('flashSuccess', 'Log successfully updated.');
                }
                else
                {
                    return view('logs.edit', [
                        'title' => 'Edit '.$log->title,
                        'log' => $log,
                        'errors' => $errors,
                    ]);
                }
            }
            else
            {
                return redirect()->route('logsHome')->with('flashError', 'You do not have access to that resource.');
            }
        }
        else
        {
            return redirect()->route('Dashboard');
        }
    }

    public function delete(Log $log)
    {
        $user = Auth()->user();

        if($user->hasPermissionTo('delete log'))
        {
            // check if log should be viewable by user
            if($user->isClientAssigned($log->client_id))
            {
                return view('logs.delete', [
                    'title'=>'Delete '.$log->title,
                    'log'  => $log
                ]);
            }
            else
            {
                return redirect()->route('logsHome')->with('flashError', 'You do not have access to that resource.');
            }
        }
        else
        {
            return redirect()->route('Dashboard');
        }
    }

    public function destroy(Log $log)
    {
        $user = Auth()->user();

        if($user->hasPermissionTo('delete log'))
        {

        }
        else
        {
            return redirect()->route('Dashboard');
        }

        // check if log should be viewable by user
        if($user->isClientAssigned($log->client_id))
        {
            if(request('delete') == 1)
            {
                $log->delete();

                return redirect('/logs')->with('flashSuccess', 'Log successfully deleted.');
            }
            else
            {
                return redirect()->route('showLog', $log->id);
            }
        }
        else
        {
            return redirect()->route('logsHome')->with('flashError', 'You do not have access to that resource.');
        }
    }
}
