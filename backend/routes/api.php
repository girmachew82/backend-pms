<?php

use App\Http\Controllers\PostController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\V1\Api\{AuthController, DriverController, OtpAuthController};


Route::post('/send-otp', [OtpAuthController::class, 'sendOtp']);
Route::post('/verify-otp', [OtpAuthController::class, 'verifyOtp']);

Route::prefix('passport')->name('passport.')->group(function(){
    Route::post('/users', [AuthController::class, 'register'])->name('api.user.register');
    Route::post('/login', [AuthController::class, 'login'])->name('api.user.login');
});

Route::middleware('auth:api')->group(function () {
    
    Route::get("/posts", [PostController::class, "index"]);
    Route::post("/posts", [PostController::class, "store"]);
    Route::get("/posts/{post}", [PostController::class, "show"]);
    Route::put("/posts/{post}", [PostController::class, "update"]);
    Route::delete("/posts/{post}", [PostController::class, "destroy"]);


    Route::get('/drivers', [DriverController::class,'index']);
    Route::post('/drivers', [DriverController::class,'update']);
    Route::get('/users', function (Request $request){
        return $request->user();
    });
});

// Route::group(['middleware' => 'auth:sanctum'], function (){

//     Route::get('/drivers', [DriverController::class,'index']);
//     Route::post('/drivers', [DriverController::class,'update']);



//     Route::get('/users', function (Request $request){
//         return $request->user();
//     });
// });

