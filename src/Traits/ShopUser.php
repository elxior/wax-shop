<?php

namespace Wax\Shop\Traits;

trait ShopUser
{
    public function paymentMethods()
    {
        return $this->hasMany(config('wax.shop.models.payment_method'));
    }
}
