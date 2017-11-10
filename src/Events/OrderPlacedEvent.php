<?php

namespace Wax\Shop\Events;

use Wax\Shop\Models\Order;
use App\User;

class OrderPlacedEvent
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
