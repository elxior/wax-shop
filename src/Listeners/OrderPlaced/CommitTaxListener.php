<?php

namespace Wax\Shop\Listeners\OrderPlaced;

use Wax\Shop\Events\OrderPlacedEvent;

class CommitTaxListener
{
    public function handle(OrderPlacedEvent $event)
    {
        $event->order()->commitTax();
    }
}
