<?php

namespace App\Common\Domain\Abstractions;

use App\Common\Domain\Contracts\CriteriaHandlerInterface;
use App\Common\Domain\Contracts\CriteriaInterface;
use App\Common\Domain\Contracts\RepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

abstract class CriteriaBase implements CriteriaInterface
{
    abstract public function apply(Model $model, RepositoryInterface $repository): void;

    protected function makeTableAlias(): string
    {
        return Uuid::uuid4();
    }
}
