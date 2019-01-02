<?php

namespace Wax\Shop\Events;

use Wax\Shop\Models\Order;

class OrderProcessedEvent
{
    protected $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function order()
    {
        return $this->order;
    }
}
