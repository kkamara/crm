<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Auth;
use App\User;
use Illuminate\Http\Request;

class UserSettingsController extends Controller
{
    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    public function show()
    {
        return view('settings.update', ['title'=>'User Settings']);
    }

    public function update()
    {
    // logic
    }
}