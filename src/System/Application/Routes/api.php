<?php
use Illuminate\Support\Facades\Route;
use System\Application\Http\Controllers\Api\BackupSettingController;

Route::middleware('auth.sanctum.cookie')->group(function (){
    Route::get('/setting/backupScheduleSetting/{backupScheduleSetting}', [BackupSettingController::class, 'show'])
        ->name('system_backup_schedule_setting');
    Route::post('/setting/backupScheduleSetting/{backupScheduleSetting}', [BackupSettingController::class, 'store']);
});
