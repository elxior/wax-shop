<?php

namespace App\Shop\Listeners;

use App\Shop\Contracts\OrderChangedEventContract;

class InvalidateOrderShippingListener
{
    public function handle(OrderChangedEventContract $event)
    {
        $event->getOrder()->invalidateShipping();
    }
}