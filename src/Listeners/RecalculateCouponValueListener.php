<?php

namespace App\Shop\Listeners;

use App\Shop\Contracts\OrderChangedEventContract;

class RecalculateCouponValueListener
{
    public function handle(OrderChangedEventContract $event)
    {
        $event->getOrder()->calculateCouponValue();
    }
}
