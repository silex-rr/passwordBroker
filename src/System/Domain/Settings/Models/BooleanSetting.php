<?php

namespace System\Domain\Settings\Models;

class BooleanSetting extends Setting
{
    public const TYPE = 'boolean';

    private bool $enable = false;

    protected $attributes = ['type' => self::TYPE];

    public function isEnable(): bool
    {
        return $this->enable;
    }

    public function setEnable(bool $enable): void
    {
        $this->enable = $enable;
    }
}
