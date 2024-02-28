<?php

namespace System\Application\Console\Commands;

use Exception;
use Identity\Application\Http\Requests\RegisterUserRequest;
use Identity\Application\Services\UserRegistrationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\Console\Command\Command as CommandAlias;
use System\Application\Services\BackupService;

class CreateBackup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'system:createBackup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a backup ';

    /**
     * Execute the console command.
     *
     * @param UserRegistrationService $registrationService
     * @return int
     */
    public function handle(BackupService $backupService): int
    {
        $password = $this->secret("Set password for backup: ", "");

        $this->info('Backup Process Started');
        $backup = $backupService->makeBackup(null, $password);
        $this->info('Backup Process Successfully Finished');
        $this->info('Backup link: ' . route('system_backup', ['backup' => $backup->backup_id]), false);
        return CommandAlias::SUCCESS;
    }
}
