<?php

namespace App\Common\Domain\Contracts;

use Illuminate\Database\Eloquent\Model;

interface CriteriaInterface
{
    public function apply(Model $model, RepositoryInterface $repository): void;
}
