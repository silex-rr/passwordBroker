<?php

namespace PasswordBroker\Infrastructure\Criteria;

use App\Common\Domain\Abstractions\CriteriaBase;
use App\Common\Domain\Contracts\RepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use PasswordBroker\Domain\Entry\Models\Entry;
use PasswordBroker\Domain\Entry\Models\Fields\Field;
use PasswordBroker\Domain\Entry\Models\Fields\Password;

class CriteriaEntryTitleOrEntryFieldTitleContains extends CriteriaBase
{
    private string $contains;
    public function __construct(string $contains)
    {
        $this->contains = '%' . str_replace(' ', '%', trim($contains)) . '%';
    }

    /**
     * @param Entry $model
     * @param RepositoryInterface $repository
     * @return void
     */
    public function apply(Model $model, RepositoryInterface $repository): void
    {
        $fields_table = (app(Password::class))->getTable();
        $repository->query()
            ->join($fields_table . ' AS fields_t', 'fields_t.entry_id', $model->getTable().'.entry_id')
            ->where(
                fn (Builder $query) =>
                    $query
                        ->where($model->getTable() . '.title', 'like', $this->contains)
                        ->orWhere( 'fields_t.title', 'like', $this->contains)
            )->groupBy($model->getTable() . '.entry_id');
    }
}
