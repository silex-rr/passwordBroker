<?php

namespace Identity\Infrastructure\Repository;

use App\Common\Domain\Abstractions\BaseRepository;
use Identity\Domain\User\Models\User;

class UserRepository extends BaseRepository
{

    /**
     * @inheritDoc
     */
    public function model(): string
    {
        return User::class;
    }
}
