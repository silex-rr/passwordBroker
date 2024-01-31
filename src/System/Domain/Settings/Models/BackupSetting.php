<?php

namespace System\Domain\Settings\Models;

use System\Domain\Settings\Models\Attributes\Backup\Email;
use System\Domain\Settings\Models\Attributes\Backup\Enable;
use System\Domain\Settings\Models\Attributes\Backup\Schedule;

class BackupSetting extends Setting
{
    public const TYPE = 'backup';
    protected Schedule $schedule;
    protected Enable $enable;
    protected Enable $email_enable;
    protected Email $email;


    protected $attributes = ['type' => self::TYPE];

    public function __construct(array $attributes = array())
    {
        parent::__construct($attributes);
        $this->appends[] = 'schedule';
        $this->appends[] = 'enable';
        $this->appends[] = 'email_enable';
        $this->appends[] = 'email';
        $this->schedule = new Schedule([]);
        $this->enable = new Enable(false);
        $this->email_enable = new Enable(false);
        $this->email = new Email('');
    }

    public function getSchedule(): Schedule
    {
        return $this->schedule;
    }

    public function setSchedule(Schedule $schedule): void
    {
        $this->schedule = $schedule;
    }

    public function getEnable(): Enable
    {
        return $this->enable;
    }

    public function setEnable(Enable $enable): void
    {
        $this->enable = $enable;
    }

    public function getEnableAttribute(): Enable
    {
        return $this->enable;
    }

    public function getScheduleAttribute(): Schedule
    {
        return $this->schedule;
    }

    public function getEmailEnable(): Enable
    {
        return $this->email_enable;
    }
    public function getEmailEnableAttribute(): Enable
    {
        return $this->email_enable;
    }

    public function setEmailEnable(Enable $email_enable): void
    {
        $this->email_enable = $email_enable;
    }

    public function getEmail(): Email
    {
        return $this->email;
    }

    public function getEmailAttribute(): Email
    {
        return $this->email;
    }

    public function setEmail(Email $email): void
    {
        $this->email = $email;
    }


}
