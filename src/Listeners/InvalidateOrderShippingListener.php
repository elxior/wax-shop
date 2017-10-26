<?php

namespace Wax\Shop\Listeners;

use Wax\Shop\Contracts\OrderChangedEventContract;

class InvalidateOrderShippingListener
{
    public function handle(OrderChangedEventContract $event)
    {
        $event->getOrder()->invalidateShipping();
    }
}
