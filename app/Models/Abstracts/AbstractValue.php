<?php

namespace App\Models\Abstracts;

use App\Models\Interfaces\ValueObject;
use JsonSerializable;

abstract class AbstractValue
    implements JsonSerializable, ValueObject
{
    protected mixed $value;

    /**
     * @return mixed
     */
    public function getValue(): mixed
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return (string)$this->value;
    }

    public function jsonSerialize(): string
    {
        return $this->__toString();
    }

    public static function fromNative($value): static
    {
        return new static($value);
    }

    public function equals(ValueObject $object): bool
    {
        //if (\get_class(static) !== \get_class($obj)) {

        if ($object::class !== static::class) {
            return false;
        }

        return $this->getNativeValue() === $object->getNativeValue();
    }

    public function getNativeValue(): string
    {
        return $this->value;
    }
}
