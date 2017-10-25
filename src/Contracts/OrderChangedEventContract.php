<?php

namespace App\Shop\Contracts;

use App\Shop\Models\Order;

interface OrderChangedEventContract
{
    public function getOrder() : Order;
}