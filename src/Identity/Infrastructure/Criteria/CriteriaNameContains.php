<?php

namespace Identity\Infrastructure\Criteria;

use App\Common\Domain\Abstractions\CriteriaBase;
use App\Common\Domain\Contracts\RepositoryInterface;
use Illuminate\Database\Eloquent\Model;

class CriteriaNameContains extends CriteriaBase
{
    private string $contains;
    public function __construct(string $contains)
    {
        $this->contains = '%' . str_replace(' ', '%', trim($contains)) . '%';
    }

    public function apply(Model $model, RepositoryInterface $repository): void
    {
        $repository->query()->where($model->getTable() . '.name', 'like', $this->contains);
    }
}
