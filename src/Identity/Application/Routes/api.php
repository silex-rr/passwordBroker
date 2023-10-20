<?php

use Identity\Application\Http\Controllers\UserApplicationController;
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
$routes = static function () {
    Route::post('/login', [UserAuthController::class, 'login'])
        ->name('login');
    Route::get('/logout', [UserAuthController::class, 'logout'])
        ->name('logout');

    Route::post('/registration', [UserController::class, 'store'])
        ->name('registration');

    Route::get('/user/{user:user_id}', [UserController::class, 'show'])
        ->name('user');

    Route::post('/token', [UserAuthController::class, 'getToken'])
        ->name('user_get_token');

    Route::delete('/user/{user:user_id}', [UserController::class, 'destroy']);
    Route::put('/user/{user:user_id}', [UserController::class, 'update']);

    Route::get('/users/search', [UserController::class, 'index'])
        ->name('user_search');

    Route::get('/me', [UserAuthController::class, 'show'])
        ->name('show_me');

    Route::get('/getPrivateRsa', [UserController::class, 'getPrivateRsa'])
        ->can('get-self-rsa-private-key')
        ->name('user_get_rsa_private_key');

    ///UserApplication

    Route::post('/userApplications/', [UserApplicationController::class, 'store'])
        ->name('userApplications');
    Route::get('/userApplication/{userApplication}', [UserApplicationController::class, 'show'])
        ->name('userApplication');

    Route::get('/userApplication/{userApplication:user_application_id}/offlineDatabaseMode', [UserApplicationController::class, 'getOfflineDatabaseStatus'])
        ->name('userApplicationOfflineDatabaseMode');
    Route::put('/userApplication/{userApplication:user_application_id}/offlineDatabaseMode', [UserApplicationController::class, 'setOfflineDatabaseStatus']);
};

//Route::middleware('api')->group($routes);
//Route::middleware('auth:sanctum')->group($routes);
Route::middleware('auth.sanctum.cookie')->group($routes);

