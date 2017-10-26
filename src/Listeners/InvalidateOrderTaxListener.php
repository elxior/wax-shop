<?php

namespace App\Shop\Listeners;

use App\Shop\Contracts\OrderChangedEventContract;

class InvalidateOrderTaxListener
{
    public function handle(OrderChangedEventContract $event)
    {
        $event->getOrder()->invalidateTax();
    }
}
