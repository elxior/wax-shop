<?php

namespace Wax\Shop\Contracts;

use Wax\Shop\Models\Order;

interface OrderChangedEventContract
{
    public function getOrder() : Order;
}
