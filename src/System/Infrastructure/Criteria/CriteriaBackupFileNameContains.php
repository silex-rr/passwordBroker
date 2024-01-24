<?php

namespace System\Infrastructure\Criteria;

use App\Common\Domain\Abstractions\CriteriaBase;
use App\Common\Domain\Contracts\RepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class CriteriaBackupFileNameContains extends CriteriaBase
{
    private string $contains;
    public function __construct(string $contains)
    {
        $this->contains = '%' . str_replace(' ', '%', trim($contains)) . '%';
    }

    public function apply(Model $model, RepositoryInterface $repository): void
    {
        $repository->query()->where(
            fn (Builder $query) =>
                $query
                    ->where($model->getTable() . '.file_name', 'like', $this->contains)
        );
    }
}
