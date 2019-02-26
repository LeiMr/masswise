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

Route::middleware('get')->group(function () {
    Route::any('user/detail', 'Api\UserController@mineDetails');
    Route::any('user/friend/detail/{id}', 'Api\UserController@details');
    Route::any('public/sms/get/{mobile}', 'Api\PublicController@getSms');
    Route::any('user/checkAuthentication', 'Api\UserController@checkAuthentication');
    Route::any('record/address', 'Api\UserController@addressList');
});
Route::middleware('post')->group(function () {
    Route::any('miniprogram/userinfo', 'Api\WeChatController@getUserInfo');
    Route::any('public/sms/check', 'Api\PublicController@checkSms');
    Route::any('user/authentication', 'Api\UserController@authentication');
    Route::any('add/address', 'Api\UserController@addressAdd');
    Route::any('update/address', 'Api\UserController@addressUpdate');
    Route::any('delete/address', 'Api\UserController@addressDel');
    Route::any('default/address', 'Api\UserController@addressDefault');
});

