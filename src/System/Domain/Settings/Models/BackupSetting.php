<?php

namespace System\Domain\Settings\Models;

use OpenApi\Attributes\Property;
use OpenApi\Attributes\Schema;
use System\Domain\Settings\Models\Attributes\Backup\Email;
use System\Domain\Settings\Models\Attributes\Backup\Enable;
use System\Domain\Settings\Models\Attributes\Backup\Password;
use System\Domain\Settings\Models\Attributes\Backup\Schedule;

#[Schema(
    schema: "System_BackupSetting",
    properties: [
        new Property(property: "schedule", ref: "#/components/schemas/System_Schedule", default: "[]", nullable: false,),
        new Property(property: "enable", ref: "#/components/schemas/System_Enable", nullable: true,),
        new Property(property: "email_enable", ref: "#/components/schemas/System_Enable", nullable: true,),
        new Property(property: "email", ref: "#/components/schemas/System_Email", default: "", nullable: false,),
        new Property(property: "archive_password", ref: "#/components/schemas/System_Password", default: "", nullable: false),
    ],
)]
class BackupSetting extends Setting
{
    public const TYPE = 'backup';
    protected Schedule $schedule;
    protected Enable $enable;
    protected Enable $email_enable;
    protected Email $email;
    protected Password $archive_password;


    protected $attributes = ['type' => self::TYPE];

    public function __construct(array $attributes = array())
    {
        parent::__construct($attributes);
        $this->appends[] = 'schedule';
        $this->appends[] = 'enable';
        $this->appends[] = 'email_enable';
        $this->appends[] = 'email';
        $this->appends[] = 'archive_password';
        $this->schedule = new Schedule([]);
        $this->enable = new Enable(false);
        $this->email_enable = new Enable(false);
        $this->email = new Email('');
        $this->archive_password = new Password('');
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

    public function getArchivePassword(): Password
    {
        return $this->archive_password;
    }
    public function getArchivePasswordAttribute(): Password
    {
        return $this->archive_password;
    }

    public function setArchivePassword(Password $archive_password): void
    {
        $this->archive_password = $archive_password;
    }




}
