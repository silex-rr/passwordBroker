<?php

namespace PasswordBroker\Infrastructure\Factories\Entry;

use App\Common\Domain\Abstractions\FactoryDomain;
use PasswordBroker\Domain\Entry\Models\Attributes\EntryGroupId;
use PasswordBroker\Domain\Entry\Models\Attributes\GroupName;
use PasswordBroker\Domain\Entry\Models\Attributes\MaterializedPath;

class EntryGroupFactory extends FactoryDomain
{

    /**
     * @inheritDoc
     */
    public function definition(): array
    {
//        dd(GroupId::fromNative($this->faker->uuid()));
        $entry_group_id = $this->faker->uuid();
        return [
            'entry_group_id' => EntryGroupId::fromNative($entry_group_id),
            'materialized_path' => MaterializedPath::fromNative($entry_group_id),
            'name' => GroupName::fromNative($this->faker->slug)
        ];
    }
}
