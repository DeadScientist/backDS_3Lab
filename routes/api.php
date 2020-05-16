<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware(['content_type.check'])->group(function () {

    Route::prefix('/user')->group(function () {
        Route::post('/register', 'UserController@register');
        Route::post('/login', 'UserController@login');
        Route::get('/refreshToken', 'UserController@refreshToken');
    });

    Route::middleware(['access.check'])->group(function () {
        Route::get('/user/logout', 'UserController@logout');

        Route::prefix('/dsstring')->group(function () {
            Route::post('/create','DSstringController@create');
            Route::get('/getall','DSstringController@getall');
            Route::post('/delete','DSstringController@delete');
        });
    });
});
