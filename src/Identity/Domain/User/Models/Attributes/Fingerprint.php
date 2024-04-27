<?php

namespace Identity\Domain\User\Models\Attributes;

use App\Models\Abstracts\AbstractValue;
use JsonException;

class Fingerprint extends AbstractValue
{
    private array $data;

    public function __construct(?string $json, ?array $data = null)
    {
        $this->value = '[]';
        if (is_null($json)) {
            if ($data) {
                try {
                    $this->value = json_encode($data, JSON_THROW_ON_ERROR);
                    $this->data = $data;

                    return;
                } catch (JsonException $e) {
                }
            }
            $this->data = [];

            return;
        }

        try {
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
