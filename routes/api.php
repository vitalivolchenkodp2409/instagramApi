<?php

use Illuminate\Http\Request;

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/instagram', 'AppController@redirectToInstagramProvider');
Route::get('/instagram/callback', 'AppController@handleProviderInstagramCallback');

Route::get('/instagram/{user}', 'AppController@getUserFromDB');
Route::post('/instagram/user', 'AppController@getUserFromDB');
