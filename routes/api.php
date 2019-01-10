<?php

use Illuminate\Http\Request;

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

// Route::get('/instagram', 'AppController@redirectToInstagramProvider');
// Route::get('/instagram/callback', 'AppController@handleProviderInstagramCallback');

Route::post('/instagram', 'AppController@getUserFromInsta');

Route::get('/instagram/users', 'AppController@getUsersFromDB');

Route::get('/instagram/data/{user}', 'AppController@getLastDataUserFromDB');
Route::post('/instagram/data/user', 'AppController@getLastDataUserFromDB');

Route::get('/instagram/dayScriptRun', 'AppController@saveDataUserInsta');
