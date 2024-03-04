<?php

namespace App\Common\Domain\Contracts;

interface OrderInterface
{
    public function apply(RepositoryInterface $repository): void;
}
