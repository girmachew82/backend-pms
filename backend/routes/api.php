<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\V1\Api\{DriverController, OtpAuthController};


Route::post('/send-otp', [OtpAuthController::class, 'sendOtp']);
Route::post('/verify-otp', [OtpAuthController::class, 'verifyOtp']);

Route::get("/hello", function (){

    return "Hello World";
});

Route::group(['middleware' => 'auth:sanctum'], function (){

    Route::get('/drivers', [DriverController::class,'index']);
    Route::post('/drivers', [DriverController::class,'update']);



    Route::get('/users', function (Request $request){
        return $request->user();
    });
});
