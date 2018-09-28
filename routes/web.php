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
    Route::get('/dashboard', 'DashboardController@index')->name('Dashboard');

    // Log routes
    Route::get('/logs', 'LogController@index')->name('logsHome');
    Route::get('/logs/create', 'LogController@create')->name('createLog');
    Route::post('/logs/create', 'LogController@store')->name('createLog');
    Route::get('/logs/{logSlug}', 'LogController@show')->name('showLog');
    Route::get('/logs/edit/{logSlug}', 'LogController@edit')->name('editLog');
    Route::patch('/logs/update/{logSlug}', 'LogController@update')->name('updateLog');
    Route::get('/logs/delete/{logSlug}', 'LogController@delete')->name('deleteLog');
    Route::delete('/logs/delete/{logSlug}', 'LogController@destroy')->name('destroyLog');

    // Client routes
    Route::get('/clients', 'ClientController@index')->name('clientsHome');
    Route::get('/clients/create', 'ClientController@create')->name('createClient');
    Route::post('/clients/create', 'ClientController@store')->name('createClient');
    Route::get('/clients/{clientSlug}', 'ClientController@show')->name('showClient');
    Route::get('/clients/edit/{logSlug}', 'ClientController@edit')->name('editClient');
    Route::patch('/clients/update/{logSlug}', 'ClientController@update')->name('updateClient');
    Route::delete('/clients/delete/{logSlug}', 'ClientController@destroy')->name('destroyClient');

    // User routes
    Route::get('/users', 'UserController@index')->name('usersHome');
    Route::get('/users/create', 'UserController@create')->name('createUser');
    Route::post('/users/create', 'UserController@store')->name('createUser');
    Route::get('/users/{user}', 'UserController@show')->name('showUser');
    Route::get('/users/edit/{user}', 'UserController@edit')->name('editUser');
    Route::patch('/users/update/{user}', 'UserController@update')->name('updateUser');
    Route::get('/users/delete/{user}', 'UserController@delete')->name('deleteUser');
    Route::delete('/users/delete/{user}', 'UserController@destroy')->name('destroyUser');

    // User settings routes
    Route::get('/settings', 'Auth\UserSettingsController@edit')->name('editSettings');
    Route::put('/settings/update', 'Auth\UserSettingsController@update')->name('updateSettings');

    // Auth routes
    Route::get('/logout', 'Auth\LoginController@logout')->name('logout');
});

// Auth routes
Route::get('/', 'Auth\LoginController@create')->name('login');
Route::post('/login', 'Auth\LoginController@store');
Route::get('/forgot', 'Auth\ResetPasswordController@create');