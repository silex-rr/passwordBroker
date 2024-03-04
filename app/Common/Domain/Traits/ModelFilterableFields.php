<?php

namespace App\Common\Domain\Traits;

trait ModelFilterableFields
{
    public function getFilterableFields(): array
    {
        if (property_exists($this, 'filterable')
            && is_array($this->filterable)
        ) {
            return $this->filterable;
        }

        return [];
    }
}
