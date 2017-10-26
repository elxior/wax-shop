<?php

namespace Wax\Shop\Listeners;

use Wax\Shop\Contracts\OrderChangedEventContract;

class RecalculateCouponValueListener
{
    public function handle(OrderChangedEventContract $event)
    {
        $event->getOrder()->calculateCouponValue();
    }
}
