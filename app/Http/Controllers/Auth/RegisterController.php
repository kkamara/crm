<?php

namespace App\Http\Controllers\Auth;

use App\User;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;
use Auth;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
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
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'user_created' => 'integer',
            'username' => 'required|string|max:191|unique:users',
            'first_name' => 'required|string|max:191',
            'last_name' => 'required|string|max:191',
            'email' => 'required|string|email|max:191|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'contact_number' => 'required|string|max:191',
            'street_name' => 'required|string|max:191',
            'building_number' => 'required|string|max:191',
            'city' => 'required|string|max:191',
            'postcode' => 'required|string|max:191'
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\User
     */
    protected function createUser(array $data)
    {
        return User::create([
            'username' => trim($data['username']),
            'first_name' => trim($data['first_name']),
            'last_name' => trim($data['last_name']),
            'email' => trim($data['email']),
            'password' => bcrypt($data['password']),
            'contact_number' => trim($data['contact_number']),
            'street_name' => trim($data['street_name']),
            'city' => trim($data['city']),
            'postcode' => trim($data['postcode'])
        ]);
    }

    public function create()
    {
        if(Auth::check()) {
            return redirect('/');
        }
        return view('auth.register')->withTitle('Register');
    }
}
