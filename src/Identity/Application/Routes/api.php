<?php

use Identity\Application\Http\Controllers\UserAuthController;
use Identity\Application\Http\Controllers\UserRegistrationController;
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
    Route::post('/', [UserRegistrationController::class, 'store']);
});

Route::post('/login', [UserAuthController::class, 'login'])
    ->name('login');
Route::get('/logout', [UserAuthController::class, 'logout']);
