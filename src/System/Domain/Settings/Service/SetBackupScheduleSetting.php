<?php

namespace System\Domain\Settings\Service;

use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use System\Domain\Settings\Events\BackupScheduleSettingWasUpdated;
use System\Domain\Settings\Models\Attributes\Backup\Schedule;
use System\Domain\Settings\Models\BackupScheduleSetting;

class SetBackupScheduleSetting implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable;

    public function __construct(
        private readonly BackupScheduleSetting $backupScheduleSetting,
        /**
         * @var int[]
         */
        private readonly array $scheduleArray,
    )
    {}

    public function handle(): void
    {
        $schedule = new Schedule($this->scheduleArray);
        $this->backupScheduleSetting->setSchedule($schedule);
        $this->backupScheduleSetting->save();
        event(new BackupScheduleSettingWasUpdated($this->backupScheduleSetting));
    }
}
