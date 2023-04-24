<?php

namespace Identity\Infrastructure\Criteria;

use App\Common\Domain\Abstractions\CriteriaBase;
use Identity\Domain\User\Models\User;
use PasswordBroker\Domain\Entry\Models\EntryGroup;
use PasswordBroker\Domain\Entry\Models\Groups\Admin;

abstract class CriteriaEntryGroup extends CriteriaBase
{
    protected string $userTable;
    protected string $roleTable;

    protected string $roleTableAlias;

    /**
     * @var EntryGroup[]
     */
    protected array $entryGroups;
    /**
     * @var string[]
     */
    protected array $entryGroupsIds;

    public function __construct()
    {
        foreach ($this->entryGroups as $entryGroup) {
            $this->entryGroupsIds[] = $entryGroup->entry_group_id->getValue();
        }
        $this->userTable = app(User::class)->getTableFullName();
        $this->roleTable = app(Admin::class)->getTableFullName();

        $this->roleTableAlias = $this->makeTableAlias();
    }

}
