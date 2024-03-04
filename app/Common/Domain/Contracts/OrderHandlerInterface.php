<?php

namespace App\Common\Domain\Contracts;

use Illuminate\Support\Collection;

interface OrderHandlerInterface
{
    /** Skip any applied order during processing */
    public function skipOrders(bool $status = true): void;

    /** Return the currently configured orders */
    public function getOrders(): Collection;

    /** Add an order to the set of orders to be applied */
    public function pushOrder(OrderInterface $order): Collection;

    /** Apply any pushed order */
    public function applyOrder(): self;
}
