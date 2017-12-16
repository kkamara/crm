<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::group(['middleware'=>'auth'],function () {

    // Home routes
    Route::get('/dashboard', 'HomeController@index')->name('Dashboard');

    // Log routes
    Route::get('/logs', 'LogController@index')->name('logsHome');
    Route::get('/logs/{log}', 'LogController@show')->name('showLog');
    Route::get('/logs/create/', 'LogController@show')->name('createLog');
    Route::get('/logs/edit/{log}', 'LogController@edit')->name('editLog');
    Route::patch('/logs/update/{log}', 'LogController@update')->name('updateLog');
    Route::delete('/logs/delete/{log}', 'LogController@destroy')->name('deleteLog');
    
    // Client routes
    Route::get('/clients', 'ClientController@index')->name('clientsHome');
    Route::get('/clients/{client}', 'ClientController@show')->name('showClient');
    Route::get('/clients/create', 'ClientController@show')->name('createClient');
    Route::get('/clients/edit/{client}', 'ClientController@edit')->name('editClient');
    Route::patch('/clients/update/{client}', 'ClientController@update')->name('updateClient');
    Route::delete('/clients/delete/{client}', 'ClientController@destroy')->name('deleteClient');

    // User routes
    // coming soon...

    // User settings routes
    Route::get('/settings', 'Auth\UserSettingsController@show')->name('showSettings');
    Route::get('/settings/edit', 'Auth\UserSettingsController@edit')->name('editSettings');
    Route::patch('/settings/update', 'Auth\UserSettingsController@update')->name('updateSettings');

    // Auth routes
    Route::get('/logout', 'Auth\LoginController@logout')->name('logout');

});

// Auth routes
Route::get('/', 'Auth\LoginController@create')->name('login');
Route::post('/login', 'Auth\LoginController@store');
Route::get('/forgot', 'Auth\ResetPasswordController@create');