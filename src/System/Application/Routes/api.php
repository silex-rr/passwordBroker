<?php
use Illuminate\Support\Facades\Route;
use System\Application\Http\Controllers\Api\BackupController;
use System\Application\Http\Controllers\Api\BackupSettingController;

Route::middleware('auth.sanctum.cookie')->group(function (){
    Route::get('/setting/backupSetting/{backupScheduleSetting}', [BackupSettingController::class, 'show'])
        ->name('system_backup_setting');
    Route::post('/setting/backupSetting/{backupScheduleSetting}', [BackupSettingController::class, 'store']);

    Route::post('/backup', [BackupController::class, 'store'])
        ->name('system_backups');
    Route::get('/backup/{backup:backup_id}', [BackupController::class, 'show'])
        ->name('system_backup');
});
