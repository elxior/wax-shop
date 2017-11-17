<?php

namespace Wax\Shop\Listeners\OrderPlaced;

use Wax\Shop\Events\OrderPlacedEvent;
use Wax\Shop\Models\Coupon;

class CouponListener
{
    public function handle(OrderPlacedEvent $event)
    {
        $order = $event->order();
        if (!$order->coupon) {
            return;
        }

        if ($order->coupon->one_time) {
            Coupon::where('code', $order->coupon->code)->delete();
        }
    }
}
