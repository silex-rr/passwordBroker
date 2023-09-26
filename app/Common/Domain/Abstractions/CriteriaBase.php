<?php

namespace App\Common\Domain\Abstractions;

use App\Common\Domain\Contracts\CriteriaInterface;
use App\Common\Domain\Contracts\RepositoryInterface;
use Illuminate\Database\Eloquent\Model;

abstract class CriteriaBase implements CriteriaInterface
{
    abstract public function apply(Model $model, RepositoryInterface $repository): void;

    protected function makeTableAlias(): string
    {
        return 'A' . bin2hex(random_bytes(10)) . 'S';

//        return str_replace('-', '_', Uuid::uuid4());
    }
}
