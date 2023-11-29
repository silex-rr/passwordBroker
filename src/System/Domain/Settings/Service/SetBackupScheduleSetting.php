<?php

namespace System\Domain\Settings\Service;

use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use System\Domain\Settings\Events\BackupScheduleSettingWasUpdated;
use System\Domain\Settings\Models\Attributes\Backup\Schedule;
use System\Domain\Settings\Models\BackupSetting;

class SetBackupScheduleSetting implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable;

    public function __construct(
        private readonly BackupSetting $backupSetting,
        /**
         * @var int[]
         */
        private readonly array         $scheduleArray,
    )
    {}

    public function handle(): void
    {
        $schedule = new Schedule($this->scheduleArray);
        $this->backupSetting->setSchedule($schedule);
        $this->backupSetting->save();
        event(new BackupScheduleSettingWasUpdated($this->backupSetting));
    }
}
