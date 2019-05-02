<?php

use Illuminate\Http\Request;
use App\Notifications\Example;
use Illuminate\Support\Facades\Auth;

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
Route::group(['middleware' => ['jwt.auth', 'api-header']], function () {

    // all routes to protected resources are registered here
    Route::get('users/list', function () {
        $users = App\User::all();

        $response = ['success' => true, 'data' => $users];
        return response()->json($response, 201);
    });

    Route::post('test', function () {
        Auth::user()->notify(new Example());
    });
});
Route::group(['middleware' => 'api-header'], function () {

    // The registration and login requests doesn't come with tokens
    // as users at that point have not been authenticated yet
    // Therefore the jwtMiddleware will be exclusive of them
    Route::post('user/login', 'UserController@login');
    Route::post('user/register', 'UserController@register');
});
