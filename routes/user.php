<?php


use App\Http\Controllers\UserController;

Route::post('register', [UserController::class, 'register']);
Route::post('login', [UserController::class, 'login']);

Route::group(['middleware' => ['auth:user']], function(){
    Route::post('logout', [UserController::class, 'logout']);
    Route::get('memberInfo', [UserController::class, 'memberInfo']);
});
