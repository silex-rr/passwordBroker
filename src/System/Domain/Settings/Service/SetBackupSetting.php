<?php

namespace System\Domain\Settings\Service;

use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use System\Domain\Settings\Events\BackupSettingScheduleWasUpdated;
use System\Domain\Settings\Events\BackupSettingWasDisabled;
use System\Domain\Settings\Events\BackupSettingWasEnabled;
use System\Domain\Settings\Models\Attributes\Backup\Enable;
use System\Domain\Settings\Models\Attributes\Backup\Schedule;
use System\Domain\Settings\Models\BackupSetting;

class SetBackupSetting implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable;

    public function __construct(
        private readonly BackupSetting $backupSetting,
        /**
         * @var int[]
         */
        private readonly array         $scheduleArray,
        private readonly bool $enable,
    )
    {}

    public function handle(): void
    {
        $schedule = new Schedule($this->scheduleArray);
        if (!$schedule->equals($this->backupSetting->getSchedule())) {
            $this->backupSetting->setSchedule($schedule);
            event(new BackupSettingScheduleWasUpdated($this->backupSetting));
        }
        $enable = new Enable($this->enable);
        if (!$enable->equals($this->backupSetting->getEnable())) {
            $this->backupSetting->setEnable($enable);
            event($enable->getValue()
                ? new BackupSettingWasEnabled($this->backupSetting)
                : new BackupSettingWasDisabled($this->backupSetting)
            );
        }
        $this->backupSetting->save();

    }
}
