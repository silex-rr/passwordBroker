<?php
use Illuminate\Support\Facades\Route;
use System\Application\Http\Controllers\Api\BackupSettingController;

Route::middleware('auth.sanctum.cookie')->group(function (){
    Route::get('/setting/backup/{backupSetting}', [BackupSettingController::class, 'show'])
        ->name('system_backup_setting');
    Route::post('/setting/backup/{backupSetting}', [BackupSettingController::class, 'store']);
});
