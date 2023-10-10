<?php

namespace PasswordBroker\Infrastructure\Factories\Entry;

use App\Common\Domain\Abstractions\FactoryDomain;
use PasswordBroker\Domain\Entry\Models\Attributes\EntryGroupId;
use PasswordBroker\Domain\Entry\Models\Attributes\EntryId as EntryIdAttribute;
use PasswordBroker\Domain\Entry\Models\Attributes\Title;
use PasswordBroker\Domain\Entry\Models\EntryGroup;


class EntryFactory extends FactoryDomain
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'entry_id' => EntryIdAttribute::fromNative($this->faker->uuid()),
            'title' => Title::fromNative($this->faker->word())
        ];
    }

    public function withEntryGroup(?EntryGroup $entryGroup = null): EntryFactory
    {
        if (is_null($entryGroup)) {
            /**
             * @var EntryGroup $entryGroup
             */
            $entryGroup = EntryGroup::factory()->create();
        }
        return $this->state(fn ($attributes) => [
            'entry_group_id' => EntryGroupId::fromNative($entryGroup->entry_group_id)
        ]);
    }
}
