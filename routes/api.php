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


    Route::post('/user-register', 'AuthController@registerUser'); // user register

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/wallet/recharge', 'WalletController@recharge'); //by razorpay
        Route::post('/wallet/verify','WalletController@verify'); //by razorpay

        Route::post('/wallet/recharge/withoutRazorpay','WalletController@walletRecharge'); // by manual
    });

});
