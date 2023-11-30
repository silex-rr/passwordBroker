<?php

namespace System\Domain\Settings\Models;

use System\Domain\Settings\Models\Attributes\Backup\Enable;
use System\Domain\Settings\Models\Attributes\Backup\Schedule;

class BackupSetting extends Setting
{
    public const TYPE = 'backup';
    protected Schedule $schedule;
    protected Enable $enable;


    protected $attributes = ['type' => self::TYPE];

    public function __construct(array $attributes = array())
    {
        parent::__construct($attributes);
        $this->appends[] = 'schedule';
        $this->appends[] = 'enable';
        $this->schedule = new Schedule([]);
        $this->enable = new Enable(false);
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
}
