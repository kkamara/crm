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

    Route::get('/logs', 'LogController@index')->name('logsHome');
    
    Route::get('/clients', 'ClientController@index')->name('clientsHome');
    Route::get('/clients/{client}', 'ClientController@show')->name('showClient');


    Route::get('/logout', 'Auth\LoginController@logout')->name('logout');
    Route::get('/settings', 'Auth\UserSettingsController@show')->name('userSettings');
});

Route::get('/', 'Auth\LoginController@create')->name('login');
Route::post('/login', 'Auth\LoginController@store');
Route::get('/forgot', 'Auth\ResetPasswordController@create');