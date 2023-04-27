<?php

namespace PasswordBroker\Domain\Entry\Models\Fields;

use PasswordBroker\Domain\Entry\Models\Fields\Attributes\FileName;
use PasswordBroker\Domain\Entry\Models\Fields\Attributes\FileSize;

/**
 * @property FileName $file_name
 * @property FileSize $file_size
 */
class File extends Field
{
    public const TYPE = 'file';

    protected $attributes = ['type' => self::TYPE];

    public function __construct(array $attributes = array())
    {
        $this->hidden = array_filter($this->hidden, static fn($v) => !in_array($v, ['file_name', 'file_size'], true));

        parent::__construct($attributes);
    }

}
