<?php

namespace Identity\Infrastructure\Criteria;

use App\Common\Domain\Contracts\CriteriaHandlerInterface;
use App\Common\Domain\Contracts\RepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\JoinClause;

class CriteriaNotInEntryGroups extends CriteriaEntryGroup
{
    public function __construct(array $notInEntryGroups)
    {
        $this->entryGroups = $notInEntryGroups;
        parent::__construct();
    }
    public function apply(Model $model, RepositoryInterface $repository): void
    {
        // TODO: Implement apply() method.
        $builder = $repository->query();
        $builder->leftJoin(
            $this->roleTable . ' as ' . $this->roleTableAlias,
            function (JoinClause $join) {
                $join->on( $this->roleTableAlias . '.user_id', '=', $this->userTable . '.user_id')
                    ->whereIn($this->roleTableAlias . '.entry_group_id',
                        $this->entryGroupsIds
                    );
            }
        );
        $builder->whereNull( $this->roleTableAlias . '.id');
    }
}
