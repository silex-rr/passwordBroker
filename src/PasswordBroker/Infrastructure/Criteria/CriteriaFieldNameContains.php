<?php

namespace PasswordBroker\Infrastructure\Criteria;

use App\Common\Domain\Contracts\RepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use PasswordBroker\Domain\Entry\Models\Fields\EntryFieldHistory;

class CriteriaFieldNameContains extends CriteriaEntryField
{
    private string $contains;
    public function __construct(string $contains)
    {
        parent::__construct();
        $this->contains = '%' . str_replace(' ', '%', trim($contains)) . '%';
    }

    /**
     * @param Model $model
     * @param RepositoryInterface $repository
     * @return void
     */
    public function apply(Model $model, RepositoryInterface $repository): void
    {
        /**
         * @var EntryFieldHistory $model
         */

        $builder = $repository->query();

        $builder->join(
            $this->entryFieldTable . ' as ' . $this->entryFieldTableAlias,
                $this->entryFieldTableAlias . '.field_id',
            '=',
                $model->getTable() . '.field_id')
            ->where($this->entryFieldTableAlias . '.title', 'like', $this->contains);
    }
}
