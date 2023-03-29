<?php

use Identity\Application\Http\Controllers\UserAuthController;
use Identity\Application\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

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

//Route::get('/', [User::class, 'index']);

Route::middleware('api')->group(function () {
    Route::post('/login', [UserAuthController::class, 'login'])
        ->name('login');
    Route::get('/logout', [UserAuthController::class, 'logout'])
        ->name('logout');

    Route::post('/registration', [UserController::class, 'store'])
        ->name('registration');

    Route::get('/user/{user:user_id}', [UserController::class, 'show'])
        ->name('user');
    Route::delete('/user/{user:user_id}', [UserController::class, 'destroy']);
    Route::put('/user/{user:user_id}', [UserController::class, 'update']);

    Route::get('/me', [UserAuthController::class, 'show'])
        ->name('show_me');
});

