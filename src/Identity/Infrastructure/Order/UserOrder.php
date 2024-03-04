<?php

namespace Identity\Infrastructure\Order;

use App\Common\Domain\Abstractions\OrderBase;
use Identity\Domain\User\Models\User;
use Illuminate\Database\Eloquent\Model;

class UserOrder extends OrderBase
{
    #[\Override] public function getModel(): ?Model
    {
        return app(User::class);
    }

}
