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
use System\Domain\Settings\Models\Attributes\Backup\Email;
use System\Domain\Settings\Models\Attributes\Backup\Enable;
use System\Domain\Settings\Models\Attributes\Backup\Password;
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
        private readonly array          $scheduleArray,
        private readonly bool           $enable,
        private readonly bool           $email_enable,
        private readonly ?string        $email,
        private readonly ?string        $archive_password,
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
        $email_enable = new Enable($this->email_enable);
        if (!$email_enable->equals($this->backupSetting->getEmailEnable())) {
            $this->backupSetting->setEmailEnable($email_enable);
//                event($email_enable->getValue()
//                    ? new BackupSettingEmailWasEnabled($this->backupSetting)
//                    : new BackupSettingEmailWasDisabled($this->backupSetting)
//                );
        }
        $email = new Email($this->email ?: '');
        if (!$email->equals($this->backupSetting->getEmail())) {
            $this->backupSetting->setEmail($email);
//            event(new BackupSettingScheduleWasUpdated($this->backupSetting));
        }
        $archivePassword = new Password($this->archive_password ?: '');
        if (!$archivePassword->equals($this->backupSetting->getArchivePassword())) {
            $this->backupSetting->setArchivePassword($archivePassword);
//            event(new BackupSettingScheduleWasUpdated($this->backupSetting));
        }


        $this->backupSetting->save();
    }
}
