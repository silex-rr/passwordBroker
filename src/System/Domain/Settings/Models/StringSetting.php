<?php

namespace System\Domain\Settings\Models;

class StringSetting extends Setting
{
    public const TYPE = 'string';
    private string $value = '';

    protected $attributes = ['type' => self::TYPE];

    public function getValue(): string
    {
        return $this->value;
    }

    public function setValue(string $value): void
    {
        $this->value = $value;
    }
}
