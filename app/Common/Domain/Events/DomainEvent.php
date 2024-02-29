<?php

namespace App\Common\Domain\Events;

use Identity\Domain\User\Models\User;
use Illuminate\Database\Eloquent\Model;

abstract class DomainEvent
{
    public const CHANNEL_DOMAIN_EVENTS = 'domain_events';

    public Model $entity;
    public ?User $user;
    public function getName(): string
    {
        return str_replace('_', '.', $this->toSnakeCase(
           (new \ReflectionClass($this))->getShortName()
        ));
    }

    private function toSnakeCase($str): string
    {
        return strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $str));
    }
}
