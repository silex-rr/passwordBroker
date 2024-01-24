<?php

namespace System\Infrastructure\Factories\Backup;

use App\Common\Domain\Abstractions\FactoryDomain;
use System\Domain\Backup\Models\Attributes\BackupId;
use System\Domain\Backup\Models\Attributes\BackupState;
use System\Domain\Backup\Models\Attributes\FileName;
use System\Domain\Backup\Models\Attributes\Size;

class BackupFactory extends FactoryDomain
{

    public function definition(): array
    {
        $states = $this->faker->shuffleArray([
            BackupState::CREATED,
            BackupState::CREATING,
            BackupState::AWAIT,
            BackupState::DELETED,
            BackupState::ERROR
        ]);
        return [
            'backup_id' => BackupId::fromNative($this->faker->uuid()),
            'file_name' => FileName::fromNative($this->faker->unique()->word()),
            'state' => array_shift($states),
            'size' => Size::fromNative($this->faker->numberBetween(100, 100000)),
        ];
    }
}
