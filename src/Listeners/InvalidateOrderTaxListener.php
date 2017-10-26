<?php

namespace Wax\Shop\Listeners;

use Wax\Shop\Contracts\OrderChangedEventContract;

class InvalidateOrderTaxListener
{
    public function handle(OrderChangedEventContract $event)
    {
        $event->getOrder()->invalidateTax();
    }
}
