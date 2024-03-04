<?php

namespace App\Common\Domain\Contracts;

interface ModelFilterableFieldsInterface
{
    public function getFilterableFields(): array;
}
