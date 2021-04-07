<?php


use App\Http\Controllers\AdminController;

Route::post('/register', [AdminController::class, 'register']);
Route::post('/login', [AdminController::class, 'login']);
Route::get('/forgotPassword/{admin}', [AdminController::class, 'forgotPassword']);

Route::group(['middleware' => ['auth:admin']], function(){
    Route::post('/logout', [AdminController::class, 'logout']);
    Route::get('/memberInfo', [AdminController::class, 'memberInfo']);
});

Route::get('sendMail', function (){
    Mail::to('abc@abc.com')->send(new \App\Mail\FirstMail());
    return 'good';
});
