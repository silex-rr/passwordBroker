<?php

namespace App\Common\Domain\Traits;

use App\Common\Domain\Jobs\SaveDomainEvent;

trait Saveable
{
    public function save()
    {
        dispatch(new SaveDomainEvent($this));
    }
}
