<?php

namespace App\Models\Interfaces;

interface ValueObject
{
    public static function fromNative($value): self;
    public function equals(ValueObject $object): bool;
    public function __toString(): string;
    public function getNativeValue();
}
