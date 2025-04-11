<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::group(['namespace' => 'App\Http\Controllers\Api'], function () {

    Route::get('/user', 'AuthController@user');

    Route::post('/user-register', 'AuthController@registerUser');

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/wallet/recharge', 'WalletController@recharge');
        Route::post('/wallet/verify','WalletController@verify');

        Route::post('/wallet/recharge/withoutRazorpay','WalletController@walletRecharge');
    });

});
