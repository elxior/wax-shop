<?php

namespace Wax\Shop\Listeners\OrderPlaced;

use Illuminate\Contracts\Queue\ShouldQueue;
use Wax\Shop\Events\OrderPlacedEvent;

class CommitTaxListener implements ShouldQueue
{
    public function handle(OrderPlacedEvent $event)
    {
        $event->order()->commitTax();
    }
}
