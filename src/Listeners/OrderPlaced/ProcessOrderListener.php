<?php

namespace Wax\Shop\Listeners\OrderPlaced;

use Wax\Shop\Events\OrderPlacedEvent;

class ProcessOrderListener
{
    public function handle(OrderPlacedEvent $event)
    {
        $order = $event->order();

        $order->process();
    }
}
