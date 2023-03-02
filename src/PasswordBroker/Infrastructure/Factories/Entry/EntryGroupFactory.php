<?php

namespace PasswordBroker\Infrastructure\Factories\Entry;

use App\Common\Domain\Abstractions\FactoryDomain;
use PasswordBroker\Domain\Entry\Models\Attributes\EntryGroupId;
use PasswordBroker\Domain\Entry\Models\Attributes\GroupName;

class EntryGroupFactory extends FactoryDomain
{

    /**
     * @inheritDoc
     */
    public function definition(): array
    {
//        dd(GroupId::fromNative($this->faker->uuid()));
        return [
            'entry_group_id' => EntryGroupId::fromNative($this->faker->uuid()),
            'name' => GroupName::fromNative($this->faker->slug)
        ];
    }
}
