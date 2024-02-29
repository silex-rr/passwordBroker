<?php

namespace System\Application\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Symfony\Component\Console\Command\Command as CommandAlias;
use System\Application\Services\BackupService;

class RecoveryFromBackupFile extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'system:recoveryFromBackupFile {filePath}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a backup ';

    /**
     * Execute the console command.
     *
     * @param BackupService $backupService
     * @return int
     */
    public function handle(BackupService $backupService): int
    {
        $filePath = $this->argument('filePath');
        $password = $this->secret("Use password for backup: ", "");

        $this->info('Recovery From Backup Started');
        try {
            $backupService->recoveryFromBackupFile($filePath, empty($password) ? null: $password);
        } catch (Exception $e) {
            $this->error('Recovery From Backup Failed: ' . $e->getMessage());
            return CommandAlias::FAILURE;
        }
        return CommandAlias::SUCCESS;
    }
}
