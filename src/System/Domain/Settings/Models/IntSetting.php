<?php

namespace System\Domain\Settings\Models;

class IntSetting extends Setting
{
    public const TYPE = 'int';
    private int $value = 0;

    protected $attributes = ['type' => self::TYPE];

    public function getValue(): int
    {
        return $this->value;
    }

    public function setValue(int $value): void
    {
        $this->value = $value;
    }
}
