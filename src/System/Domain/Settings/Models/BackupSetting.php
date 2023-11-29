<?php

namespace System\Domain\Settings\Models;

use System\Domain\Settings\Models\Attributes\Backup\Schedule;

class BackupSetting extends Setting
{
    public const TYPE = 'backup';
    protected Schedule $schedule;

    protected $attributes = ['type' => self::TYPE];

    public function __construct(array $attributes = array())
    {
        parent::__construct($attributes);
        $this->appends[] = 'schedule';
        $this->schedule = new Schedule([]);
    }

    public function getSchedule(): Schedule
    {
        return $this->schedule;
    }

    public function setSchedule(Schedule $schedule): void
    {
        $this->schedule = $schedule;
    }

    public function getScheduleAttribute(): Schedule
    {
        return $this->schedule;
    }
}
