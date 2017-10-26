<?php

namespace App\Shop\Traits;

use App\Shop\Models\Order;
use Illuminate\Database\Eloquent\Model;

trait ValidatesCoupons
{
    public function validateCouponForOrder(Model $coupon, Order $order)
    {
        //validate expiration
        if (!is_null($coupon->expired_at)) {
            if (is_null($order->placed_at) && $coupon->expired_at->isPast()) {
                return false;
            }

            if (!is_null($order->placed_at) && $coupon->expired_at->lt($order->placed_at)) {
                return false;
            }
        }

        // validate minimum order
        if ($order->discountable_total < $coupon->minimum_order) {
            return false;
        }

        return true;
    }
}
