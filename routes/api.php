<?php

use Illuminate\Http\Request;

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

// Route::get('/instagram', 'AppController@redirectToInstagramProvider');
// Route::get('/instagram/callback', 'AppController@handleProviderInstagramCallback');

Route::post('/instagram', 'AppController@getUserFromInsta');

Route::get('/instagram/data/{user}', 'AppController@getDataUserFromDB');
Route::post('/instagram/data/user', 'AppController@getDataUserFromDB');
