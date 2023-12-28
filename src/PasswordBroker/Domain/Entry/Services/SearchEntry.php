<?php

namespace PasswordBroker\Domain\Entry\Services;

use Identity\Infrastructure\Criteria\CriteriaInEntryGroups;
use Identity\Infrastructure\Criteria\CriteriaNameOrEmailContains;
use Identity\Infrastructure\Criteria\CriteriaNotInEntryGroups;
use Identity\Infrastructure\Repository\UserRepository;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use PasswordBroker\Domain\Entry\Models\Entry;
use PasswordBroker\Domain\Entry\Models\EntryGroup;
use PasswordBroker\Domain\Entry\Models\Fields\Field;
use PasswordBroker\Domain\Entry\Models\Fields\Password;
use PasswordBroker\Infrastructure\Criteria\CriteriaEntryTitleOrEntryFieldTitleContains;
use PasswordBroker\Infrastructure\Repositories\EntryRepository;

class SearchEntry implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use SerializesModels;

    public function __construct(
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

//        $userRepository->applyCriteria();
//        $userRepository->query()->dd();
        $withArr = Field::getRelatedForWith();
        $withArr[] = 'entryGroup';
        $entryRepository->query()->with($withArr);
//        $entryRepository->applyCriteria();
//        $entryRepository->query()->dd();

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
