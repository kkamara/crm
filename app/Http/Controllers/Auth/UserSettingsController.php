<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use App\User;
use Auth;

class UserSettingsController extends Controller
{
    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    public function edit()
    {
        return view('settings.edit', [
            'title'=>'User Settings',
            'user' =>auth()->user(),
        ]);
    }

    public function update(Request $request)
    {
        $user = auth()->user();
        
        $validator = Validator::make($request->all(), [
            'first_name' => 'max:191',
            'last_name' => 'max:191',
            'email' => 'required|email|max:191',
            // optional
            'building_number' => 'max:191',
            'street_name' => 'max:191',
            'city' => 'max:191',
            'postcode' => 'max:191',
            'contact_number' => 'max:191',
        ]);        
        $errors = $validator->errors()->all();
        
        if(empty($errors))
        {
            $user->updateUser($request);
            
            return redirect()->back()->with('flashSuccess', 'Your settings have been updated.');
        }
        else
        {
            return view('settings.edit', [
                'title'=>'User Settings',
                'user' =>auth()->user(),
                'errors' => $validator->errors()->all(),
            ]);
        }
    }
}