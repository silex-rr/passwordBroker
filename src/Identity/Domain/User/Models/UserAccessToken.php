<?php

namespace Identity\Domain\User\Models;

use App\Common\Domain\Traits\ModelDomainConstructor;
use Laravel\Sanctum\PersonalAccessToken;

class UserAccessToken extends PersonalAccessToken
{
    use ModelDomainConstructor;
}
