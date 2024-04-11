<?php

namespace System\Application\Console\Commands;

use Carbon\Carbon;
use Identity\Domain\User\Models\User;
use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\Console\Command\Command as CommandAlias;
use System\Application\Services\BackupService;
use System\Domain\Backup\Models\Backup;
use System\Domain\Backup\Service\CreateBackup;
use System\Domain\Backup\Service\MakeBackup;
use System\Domain\Settings\Models\BackupSetting;

class BackupCron extends Command
{
    use DispatchesJobs;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'system:backupCron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cron manager for Backup';

    /**
     * Execute the console command.
     *
     * @param BackupService $backupService
     * @return int
     */
    public function handle(BackupService $backupService, ): int
    {
        /**
         * @var BackupSetting $backupSetting
         */
        $backupSetting = BackupSetting::firstOrCreate([
            'key' => BackupSetting::TYPE,
            'type' => BackupSetting::TYPE,
        ]);

        $carbon = Carbon::now();

        if (!$backupSetting->getEnable()->getValue()
            || !in_array($carbon->hour, $backupSetting->getSchedule()->getValue(), true)
            || Backup::where('created_at', '>=', $carbon->format("Y-m-d H:00:00"))->exists()
        ) {
            return Command::SUCCESS;
        }

        $user = User::where("is_admin", true)->firstOrFail();

        Auth::login($user);

        $this->dispatchSync(new CreateBackup(backup: new Backup(), backupService: $backupService));

        return CommandAlias::SUCCESS;
    }
}
