<?php

namespace PasswordBroker\Infrastructure\Factories\Entry;

use App\Common\Domain\Abstractions\FactoryDomain;
use PasswordBroker\Domain\Entry\Models\Attributes\EntryGroupId;
use PasswordBroker\Domain\Entry\Models\Attributes\EntryId as EntryIdAttribute;
use PasswordBroker\Domain\Entry\Models\EntryGroup;
use PasswordBroker\Domain\Entry\Models\Fields\Attributes\Title;


class EntryFactory extends FactoryDomain
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        /**
         * @var EntryGroup $entryGroup
         */
        $entryGroup = EntryGroup::factory()->create();
        return [
            'entry_id' => EntryIdAttribute::fromNative($this->faker->uuid()),
            'entry_group_id' => EntryGroupId::fromNative($entryGroup->entry_group_id),
            'title' => Title::fromNative($this->faker->word())
        ];
    }
}
