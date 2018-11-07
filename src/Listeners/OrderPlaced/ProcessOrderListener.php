<?php

namespace Wax\Shop\Listeners\OrderPlaced;

use Wax\Shop\Events\OrderPlacedEvent;

class ProcessOrderListener
{
    public function handle(OrderPlacedEvent $event)
    {
        if (config('wax.shop.payment.prior_auth_capture')) {
            return;
        }

        $order = $event->order();
        $order->process();
    }
}
