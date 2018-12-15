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

        if($user->hasPermissionTo('view log'))
        {
            $logs = Log::getUserAccessibleLogs($user);

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
                        $title = filter_var(request('title'), FILTER_SANITIZE_STRING);
                        Log::create([
                            'slug' => strtolower(str_slug($title, '-')),
                            'client_id' => request('client_id'),
                            'user_created' => $user->id,
                            'title' => $title,
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
    public function show($logSlug)
    {
        $log = Log::where('slug', $logSlug)->first();

        if($log !== null)
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
        else
        {
            return redirect()->route('logsHome')->with('flashError', 'Resource not found.');
        }
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

        if($log !== null)
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
        else
        {
            return redirect()->route('clientsHome')->with('flashError', 'Resource not found.');
        }
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

        if($log !== null)
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

                        return redirect('/logs/'.$log->slug)->with('flashSuccess', 'Log successfully updated.');
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
        else
        {
            return redirect()->route('clientsHome')->with('flashError', 'Resource not found.');
        }
    }

    public function delete($logSlug)
    {
        $log = Log::where('slug', $logSlug)->first();

        if($log !== null)
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
        else
        {
            return redirect()->route('clientsHome')->with('flashError', 'Resource not found.');
        }
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
        if(!$user->isClientAssigned($log->client_id))
        {
            return redirect()
                ->route('logsHome')
                ->with('flashError', 'You do not have access to that resource.');
        }

        $log->delete();

        return redirect()->route('logsHome')->with('flashSuccess', 'Log successfully deleted.');        
    }
}
