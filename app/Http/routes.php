<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('/', function () {
    return 'hi';
});

Route::post('/upload', 'MainController@upload');

Route::any('/{sha1}', 'MainController@view')->where(['sha1' => '\w+']);


