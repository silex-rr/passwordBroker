<?php

namespace System\Application\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Symfony\Component\Console\Command\Command as CommandAlias;
use System\Application\Services\RecoveryService;

class RecoveryFromBackupFile extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'system:recoveryFromBackup {?filePath}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a backup ';

    /**
     * Execute the console command.
     *
     * @param RecoveryService $recoveryService
     * @return int
     */
    public function handle(RecoveryService $recoveryService): int
    {
        $filePath = $this->argument('filePath');
        if (empty($filePath)) {
            $filePath = $this->ask('Enter path to backup file: ');
        }
        $password = $this->secret("Use password for backup: ", "");

        $this->info('Recovery From Backup Started');
        try {
            $recoveryService->recoveryFromBackupFile($filePath, empty($password) ? null: $password);
        } catch (Exception $e) {
            $this->error('Recovery From Backup Failed: ' . $e->getMessage());
            return CommandAlias::FAILURE;
        }
        return CommandAlias::SUCCESS;
    }
}
