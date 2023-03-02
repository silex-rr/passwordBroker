<?php

use Illuminate\Support\Facades\Route;
use PasswordBroker\Application\Http\Controllers\Api\EntryController;
use PasswordBroker\Application\Http\Controllers\Api\EntryFieldController;
use PasswordBroker\Application\Http\Controllers\Api\EntryGroupController;
use PasswordBroker\Application\Http\Controllers\Api\EntryGroupUserController;

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
Route::middleware('api')->group(function (){

    Route::get('/entryGroups', [EntryGroupController::class, 'index'])
        ->name('entryGroups');
    Route::post('/entryGroups', [EntryGroupController::class, 'store']);
    Route::get('/entryGroups/{entryGroup:entry_group_id}', [EntryGroupController::class, 'show'])
        ->name('entryGroup');
    Route::put('/entryGroups/{entryGroup:entry_group_id}', [EntryGroupController::class, 'update']);
    Route::patch('/entryGroups/{entryGroup:entry_group_id}', [EntryGroupController::class, 'move']);
    Route::delete('/entryGroups/{entryGroup:entry_group_id}', [EntryGroupController::class, 'destroy']);

    Route::post('/entryGroups/{entryGroup:entry_group_id}/users/', [EntryGroupUserController::class, 'store'])
        ->name('entryGroupUsers');
    Route::delete('/entryGroups/{entryGroup:entry_group_id}/users/{user:user_id}',
        [EntryGroupUserController::class, 'destroy'])
        ->name('entryGroupUser');

    Route::get('/entryGroups/{entryGroup:entry_group_id}/entries', [EntryController::class, 'index'])
        ->name('entryGroupEntries');
    Route::post('/entryGroups/{entryGroup:entry_group_id}/entries', [EntryController::class, 'store']);
    Route::get('/entryGroups/{entryGroup:entry_group_id}/entries/{entry:entry_id}', [EntryController::class, 'show'])
        ->name('entryGroupEntry');
    Route::put('/entryGroups/{entryGroup:entry_group_id}/entries/{entry:entry_id}', [EntryController::class, 'update']);
    Route::patch('/entryGroups/{entryGroup:entry_group_id}/entries/{entry:entry_id}', [EntryController::class, 'move']);
    Route::delete('/entryGroups/{entryGroup:entry_group_id}/entries/{entry:entry_id}', [EntryController::class, 'destroy']);

    Route::get('/entryGroups/{entryGroup:entry_group_id}/entries/{entry:entry_id}/fields',
        [EntryFieldController::class, 'index'])
        ->name('entryFields');
    Route::post('/entryGroups/{entryGroup:entry_group_id}/entries/{entry:entry_id}/fields',
        [EntryFieldController::class, 'store']);
    Route::get('/entryGroups/{entryGroup:entry_group_id}/entries/{entry:entry_id}/fields/{field:field_id}',
        [EntryFieldController::class, 'show'])
        ->name('entryField');
    Route::put('/entryGroups/{entryGroup:entry_group_id}/entries/{entry:entry_id}/fields/{field:field_id}',
        [EntryFieldController::class, 'update']);
    Route::delete('/entryGroups/{entryGroup:entry_group_id}/entries/{entry:entry_id}/fields/{field:field_id}',
        [EntryFieldController::class, 'destroy']);
});




//Route::get('/', 'PasswordBroker\Application\Http\Controllers\Api\PasswordController@index');
