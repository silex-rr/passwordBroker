<?php

use Illuminate\Support\Facades\Route;
use PasswordBroker\Application\Http\Controllers\Api\EntryBulkController;
use PasswordBroker\Application\Http\Controllers\Api\EntryController;
use PasswordBroker\Application\Http\Controllers\Api\EntryFieldController;
use PasswordBroker\Application\Http\Controllers\Api\EntryFieldHistoryController;
use PasswordBroker\Application\Http\Controllers\Api\EntryGroupController;
use PasswordBroker\Application\Http\Controllers\Api\EntryGroupHistoryController;
use PasswordBroker\Application\Http\Controllers\Api\EntryGroupUserController;
use PasswordBroker\Application\Http\Controllers\Api\EntrySearchController;
use PasswordBroker\Application\Http\Controllers\Api\ImportController;

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
//Route::middleware('api')->group(function () {
Route::middleware('auth.sanctum.cookie')->group(function () {

    Route::get('/entryGroups', [EntryGroupController::class, 'index'])
        ->name('entryGroups');
    Route::get('/entryGroupsWithFields', [EntryGroupController::class, 'indexWithFields'])
        ->can('get-groups-with-fields')
        ->name('allGroupsWithFields');
    Route::get('/entryGroupsAsTree', [EntryGroupController::class, 'indexAsTree'])
        ->name('entryGroupsAsTree');
    Route::post('/entryGroups', [EntryGroupController::class, 'store']);
    Route::get('/entryGroups/{entryGroup:entry_group_id}', [EntryGroupController::class, 'show'])
        ->name('entryGroup');
    Route::get('/entryGroups/{entryGroup:entry_group_id}/history', [EntryGroupHistoryController::class, 'index'])
        ->name('entryGroupHistory');

    Route::put('/entryGroups/{entryGroup:entry_group_id}', [EntryGroupController::class, 'update']);
    Route::patch('/entryGroups/{entryGroup:entry_group_id}', [EntryGroupController::class, 'move']);
    Route::delete('/entryGroups/{entryGroup:entry_group_id}', [EntryGroupController::class, 'destroy']);

    Route::get('/entryGroups/{entryGroup:entry_group_id}/users/', [EntryGroupUserController::class, 'index']);
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
    Route::delete('/entryGroups/{entryGroup:entry_group_id}/entries/{entry:entry_id}',
        [EntryController::class, 'destroy']);

    Route::post('/entryGroups/{entryGroup:entry_group_id}/entries/bulkEdit/delete',
        [EntryBulkController::class, 'bulkDestroy'])
    ->name('entryGroupEntriesBulkDestroy');
    Route::post('/entryGroups/{entryGroup:entry_group_id}/entries/bulkEdit/move',
        [EntryBulkController::class, 'bulkMove'])
    ->name('entryGroupEntriesBulkMove');

    Route::get('/entrySearch', [EntrySearchController::class, 'index'])
        ->name('entrySearch');

    Route::get('/entryGroups/{entryGroup:entry_group_id}/entries/{entry:entry_id}/fields',
        [EntryFieldController::class, 'index'])
        ->name('entryFields');
    Route::post('/entryGroups/{entryGroup:entry_group_id}/entries/{entry:entry_id}/fields',
        [EntryFieldController::class, 'store']);
    Route::get('/entryGroups/{entryGroup:entry_group_id}/entries/{entry:entry_id}/fields/{field:field_id}',
        [EntryFieldController::class, 'show'])
        ->name('entryField');
    Route::post('/entryGroups/{entryGroup:entry_group_id}/entries/{entry:entry_id}/fields/{field:field_id}/decrypted',
        [EntryFieldController::class, 'showDecrypted'])
        ->name('entryFieldDecrypted');
    Route::post('/entryGroups/{entryGroup:entry_group_id}/entries/{entry:entry_id}/fields/{field:field_id}/totp',
        [EntryFieldController::class, 'showTOTP'])
        ->name('entryFieldTOTP');
    Route::put('/entryGroups/{entryGroup:entry_group_id}/entries/{entry:entry_id}/fields/{field:field_id}',
        [EntryFieldController::class, 'update']);
    Route::delete('/entryGroups/{entryGroup:entry_group_id}/entries/{entry:entry_id}/fields/{field:field_id}',
        [EntryFieldController::class, 'destroy']);

    Route::get('/entryGroup/history', [EntryGroupHistoryController::class, 'search'])
        ->can('field-history-search-any')
        ->name('entryFieldHistorySearch');

    Route::get('/entryGroups/{entryGroup:entry_group_id}/entries/{entry:entry_id}/fields/{field:field_id}/history',
        [EntryFieldHistoryController::class, 'index']
    )->name('entryFieldHistory');
    Route::post('/entryGroups/{entryGroup:entry_group_id}/entries/{entry:entry_id}/fields/{field:field_id}/history/'
        . '{fieldEditLog:field_edit_log_id}/decrypted',
        [EntryFieldHistoryController::class, 'showDecrypted'])
        ->name('entryFieldHistoryDecrypted');

    Route::post('/import', [ImportController::class, 'store'])
        ->name('import');
});




//Route::get('/', 'PasswordBroker\Application\Http\Controllers\Api\PasswordController@index');
