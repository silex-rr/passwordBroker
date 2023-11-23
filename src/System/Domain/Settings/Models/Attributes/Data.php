<?php

namespace System\Domain\Settings\Models\Attributes;

use App\Models\Abstracts\AbstractValue;
use JsonException;

class Data extends AbstractValue
{
    private array $data;
    public function __construct(?string $json)
    {
        $this->value = '[]';
        if (is_null($json)) {
            $this->data = [];
            return;
        }

        try {
            if($json) {
                $arr = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
            }
            $this->data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
            $this->value = $json;
        } catch (JsonException|\Exception $e) {
            $this->data = [];
        }
    }

    public function getData(): array
    {
        return $this->data;
    }


}
