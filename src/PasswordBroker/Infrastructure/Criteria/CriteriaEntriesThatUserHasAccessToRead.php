<?php

namespace PasswordBroker\Infrastructure\Criteria;

use App\Common\Domain\Abstractions\CriteriaBase;
use App\Common\Domain\Contracts\RepositoryInterface;
use Identity\Domain\User\Models\User;
use Illuminate\Database\Eloquent\Model;
use PasswordBroker\Domain\Entry\Models\Entry;
use PasswordBroker\Domain\Entry\Models\EntryGroup;
use PasswordBroker\Domain\Entry\Models\Groups\Admin;

class CriteriaEntriesThatUserHasAccessToRead extends CriteriaBase
{
    public function __construct(private readonly User $user)
    {}

    /**
     * @param Entry $model
     * @param RepositoryInterface $repository
     * @return void
     */
    public function apply(Model $model, RepositoryInterface $repository): void
    {
        $groups_table = (app(EntryGroup::class))->getTable();
        $group_users_table = (app(Admin::class))->getTable();
        $repository->query()
            ->join($groups_table . ' AS group_t', 'group_t.entry_group_id', $model->getTable().'.entry_group_id')
            ->join( $group_users_table . ' AS group_u', 'group_u.entry_group_id', 'group_t.entry_group_id')
            ->where(
                'group_u.user_id', '=', $this->user->user_id->getNativeValue()
            );
    }
}
