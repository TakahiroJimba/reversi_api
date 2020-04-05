<?php

use Illuminate\Http\Request;

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

// ユーザ登録
Route::post('registration_user', 'Api\UserController@regist');

// ログイン認証
Route::post('login_auth',    'Api\LoginController@auth');

// ルーム関連
Route::post('create_room',   'Api\RoomController@create');
Route::post('watch_room',    'Api\RoomController@watch');

// ゲーム関連
Route::post('get_priority',  'Api\GameController@getPriority');
Route::post('set_turn',      'Api\GameController@setTurn');
