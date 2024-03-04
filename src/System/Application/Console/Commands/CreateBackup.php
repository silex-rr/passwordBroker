<?php

namespace System\Application\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Symfony\Component\Console\Command\Command as CommandAlias;
use System\Application\Services\BackupService;
use System\Domain\Backup\Models\Backup;
use System\Domain\Backup\Service\CreateBackup as CreateBackupService;
use System\Domain\Backup\Service\MakeBackup;

class CreateBackup extends Command
{
    use DispatchesJobs;
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
     * @param BackupService $backupService
     * @return int
     */
    public function handle(BackupService $backupService, ): int
    {
        $password = $this->secret("Set password for backup: ", "");

        $this->info('Backup Process Started');

        $backup = $this->dispatchSync(new CreateBackupService(backup: new Backup(), backupService: $backupService, doNotMakeBackup: true));
        $this->dispatchSync(new MakeBackup(
                backup: $backup,
                backupService: $backupService,
                password: $password ?? null
            )
        );
        $this->info('Backup Process Successfully Finished');
        $this->info('Backup link: ' . route('system_backup', ['backup' => $backup->backup_id]), false);
        return CommandAlias::SUCCESS;
    }
}
