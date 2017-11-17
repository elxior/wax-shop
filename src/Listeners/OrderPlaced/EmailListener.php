<?php

namespace Wax\Shop\Listeners\OrderPlaced;

use Illuminate\Support\Facades\Mail;
use Wax\Shop\Events\OrderPlacedEvent;
use Wax\Shop\Mail\OrderPlaced;

class EmailListener
{
    public function handle(OrderPlacedEvent $event)
    {
        $order = $event->order();

        if (empty($order->email)) {
            return false;
        }

        Mail::to($order->email)
            ->send(new OrderPlaced($order));
    }
}
