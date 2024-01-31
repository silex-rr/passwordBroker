<?php
use Illuminate\Support\Facades\Route;
use System\Application\Http\Controllers\Api\BackupController;
use System\Application\Http\Controllers\Api\BackupSettingController;

Route::middleware('auth.sanctum.cookie')->group(function (){

    Route::middleware('can:perform-with-backups')->group(static function () {
        Route::get('/setting/backupSetting/{backupSetting}', [BackupSettingController::class, 'show'])
            ->name('system_backup_setting');
        Route::post('/setting/backupSetting/{backupSetting}', [BackupSettingController::class, 'store']);
        Route::get('/backups', [BackupController::class, 'index'])
            ->name('system_backups');
        Route::post('/backups', [BackupController::class, 'store']);
        Route::get('/backups/{backup:backup_id}', [BackupController::class, 'show'])
            ->name('system_backup');
    });
});
