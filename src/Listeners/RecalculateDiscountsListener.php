<?php

namespace Wax\Shop\Listeners;

use Wax\Shop\Contracts\OrderChangedEventContract;

class RecalculateDiscountsListener
{
    public function handle(OrderChangedEventContract $event)
    {
        $event->getOrder()->calculateDiscounts();
    }
}
