<?php

namespace PasswordBroker\Domain\Entry\Models\Fields;

use OpenApi\Attributes\Property;
use OpenApi\Attributes\Schema;
use PasswordBroker\Domain\Entry\Models\Fields\Attributes\FileName;
use PasswordBroker\Domain\Entry\Models\Fields\Attributes\FileSize;

/**
 * @property FileName $file_name
 * @property FileSize $file_size
 */
#[Schema(
    schema: "PasswordBroker_File",
    allOf: [
        new Schema(ref: "#/components/schemas/PasswordBroker_Field"),
        new Property(property: "file_name", ref: "#/components/schemas/PasswordBroker_FileName",),
        new Property(property: "file_size", ref: "#/components/schemas/PasswordBroker_FileSize",),
        new Property(property: "file_mime", ref: "#/components/schemas/PasswordBroker_FileMime",),
    ],
)]
class File extends Field
{
    public const TYPE = 'file';

    protected $attributes = ['type' => self::TYPE];

    public function __construct(array $attributes = array())
    {
        $this->hidden = array_filter($this->hidden, static fn($v) => !in_array($v, ['file_name', 'file_size', 'file_mime'], true));

        parent::__construct($attributes);
    }

}
