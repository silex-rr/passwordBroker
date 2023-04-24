<?php

namespace Identity\Infrastructure\Criteria;

use App\Common\Domain\Contracts\CriteriaHandlerInterface;
use App\Common\Domain\Contracts\RepositoryInterface;
use Illuminate\Database\Eloquent\Model;

class CriteriaInEntryGroups extends CriteriaEntryGroup
{

    public function __construct(array $inEntryGroups)
    {
        $this->entryGroups = $inEntryGroups;
        parent::__construct();
    }
    public function apply(Model $model, RepositoryInterface $repository): void
    {
        $builder = $repository->query();

        $builder->joinWhere(
            $this->roleTable . ' as ' . $this->roleTableAlias,
            $this->roleTableAlias . '.user_id',
            '=',
            $this->userTable . '.user_id')
            ->whereIn($this->roleTableAlias . '.entry_group_id',
                $this->entryGroupsIds
            );
    }
}
