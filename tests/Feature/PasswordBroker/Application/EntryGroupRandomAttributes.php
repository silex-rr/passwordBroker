<?php

namespace Tests\Feature\PasswordBroker\Application;

use PasswordBroker\Domain\Entry\Models\EntryGroup;

trait EntryGroupRandomAttributes
{
    /**
     * Helper
     * @return array
     */
    private function getEntryGroupRandomAttributes(): array
    {
        /**
         * @var EntryGroup $entryGroup
         */
        $attributes = EntryGroup::factory()->raw();
        unset($attributes['entry_group_id'], $attributes['materialized_path']);
        return $attributes;
    }
}
