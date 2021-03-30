<?php


use App\Http\Controllers\AdminController;

Route::post('/register', [AdminController::class, 'register']);
Route::post('/login', [AdminController::class, 'login']);

Route::group(['middleware' => ['auth:admin']], function(){
    Route::post('/logout', [AdminController::class, 'logout']);
    Route::get('/memberInfo', [AdminController::class, 'memberInfo']);
});
