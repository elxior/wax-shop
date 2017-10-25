<?php

namespace App\Shop\Events\OrderChanged;

use App\Shop\Contracts\OrderChangedEventContract;
use App\Shop\Models\Order;

/**
 * OrderChangedEvent represents various types of changes to the active order, such as adding items to the cart,
 * changing the shipping details, and so on. The OrderChangedEvent class can be extended for specific types of events,
 * e.g. CartContentsChangedEvent, which can either have specific listeners, or you can target the
 * OrderChangedEventContract to catch all of the event types with a single listener.
 */
class OrderChangedEvent implements OrderChangedEventContract
{
    protected $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function getOrder() : Order
    {
        return $this->order;
    }
}