<?php

namespace PasswordBroker\Infrastructure\Criteria;

use App\Common\Domain\Abstractions\CriteriaBase;
use PasswordBroker\Domain\Entry\Models\Fields\Password;

abstract class CriteriaEntryField extends CriteriaBase
{
    protected string $entryFieldTable;
    protected string $entryFieldTableAlias;


    public function __construct()
    {
        $this->entryFieldTable = app(Password::class)->getTableFullName();

        $this->entryFieldTableAlias = $this->makeTableAlias();
    }
}
