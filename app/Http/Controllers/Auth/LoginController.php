<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Auth;
use App\User;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('create', 'logout');
    }

    public function create()
    {
        if(Auth::check()) {
            return redirect()->route('clientsHome');
        }

        return view('auth/login')->withTitle('Login');
    }

    public function store(Request $request)
    {
        if((request('email') && request('password')) == FALSE)
        {
            return back()->with('errors', ['Missing email and password.']);
        }

        if(!$user = User::where('email', request('email'))->first()) 
        {
            return back()->with('errors', ['Invalid login credentials provided.']);
        }

        if(!Auth::attempt(['email' => $user->email, 'password' => request('password')], 
            (int) request('remember_me')))
        {
            return back()->with('errors', ['Invalid login credentials provided.']);
        }
    
        return redirect()
                ->route('clientsHome')
                ->with('message', 'You have logged in, '.$user->first_name.' '.$user->last_name.'!');        
    }

    public function logout()
    {
        Auth::logout();

        return redirect('/');
    }
}
