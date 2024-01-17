<?php

namespace PasswordBroker\Domain\Entry\Services;

use Identity\Domain\User\Models\User;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use PasswordBroker\Domain\Entry\Models\Fields\Field;
use PasswordBroker\Infrastructure\Criteria\CriteriaEntriesThatUserHasAccessToRead;
use PasswordBroker\Infrastructure\Criteria\CriteriaEntryTitleOrEntryFieldTitleContains;
use PasswordBroker\Infrastructure\Repositories\EntryRepository;

class SearchEntry implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use SerializesModels;

    public function __construct(
        protected User $user,
        protected string $query,
        protected int $perPage,
        protected int $page,
    )
    {
    }

    public function handle(): Paginator
    {
        $entryRepository = new EntryRepository(app());
        if (!empty($this->query)) {
            $entryRepository->pushCriteria(new CriteriaEntryTitleOrEntryFieldTitleContains($this->query));
        }
        $entryRepository->pushCriteria(new CriteriaEntriesThatUserHasAccessToRead($this->user));

//        $userRepository->applyCriteria();
//        $userRepository->query()->dd();
        $withArr = Field::getRelatedForWith();
        $withArr[] = 'entryGroup';
        $entryRepository->query()->with($withArr);

//        $userId = $this->user->user_id->getNativeValue();
//
//        $entryGroupUserClosure = fn($q) => $q->where('user_id', '=', $userId);
//        $entryRepository->query()->whereHas('entryGroup.admins', $entryGroupUserClosure)
//            ->orWhereHas('entryGroup.moderators', $entryGroupUserClosure)
//            ->orWhereHas('entryGroup.members', $entryGroupUserClosure);
//        $entryRepository->applyCriteria();
//        $builder = $entryRepository->query();
//        $toSql = $builder->toSql();
//        $bindings = $builder->getBindings();
//        $all = $builder->get()->all();
//        $result = DB::select('
//            select password_broker_entries.*
//            from password_broker_entries
//
//            inner join password_broker_entry_groups as group_t on group_t.entry_group_id = password_broker_entries.entry_id
//            inner join password_broker_entry_group_user as group_u on group_u.entry_group_id = group_t.entry_group_id
//            where
//
//                group_u.user_id = ?
//                and password_broker_entries.deleted_at is null
//            group by password_broker_entries.entry_id
//        ', [$bindings[2]]);
//        dd($all, $toSql, $bindings, $result,  Entry::where('title', 'aaaa')->with('entryGroup.admins')->first());
        //->dd();


        return $entryRepository->paginate(perPage: $this->perPage, columns: ['*'], pageName: 'page', page: $this->page);

//        $builder = User::query();
//
//        $builder->select(app(User::class)->getTable() . '.*');
//
//        if (!empty($this->query)) {
//            $query = $this->query;
//            $query = str_replace(' ', '%', trim($query));
//            $builder->where('name', 'like', '%' . $query . '%');
//        }
//        $user_table = app(User::class)->getTableFullName();
//        $role_table = app(Admin::class)->getTableFullName();
//
//        if ($this->entryGroupExclude) {
//            $builder->leftJoin(
//                $role_table . ' as role_ex',
//                    function (JoinClause $join) use ($user_table) {
//                        $join->on('role_ex.user_id', '=', $user_table . '.user_id')
//                            ->where('role_ex.entry_group_id',
//                                '=',
//                                $this->entryGroupExclude->entry_group_id->getValue()
//                            );
//                    }
//            );
//            $builder->whereNull('role_ex.id');
//        }
//        if ($this->entryGroupInclude) {
//            $builder->joinWhere(
//                $role_table . ' as role_in',
//                'role_in.user_id',
//                '=',
//                $user_table . '.user_id')
//                ->where('role_in.entry_group_id',
//                    '=',
//                    $this->entryGroupInclude->entry_group_id->getValue()
//                );
//        }
//        return $builder->simplePaginate($this->perPage, ['*'], $pageName = 'page', $page = $this->page);
    }

}
