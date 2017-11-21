<?php

namespace Wax\Shop\Listeners\OrderPlaced;

use Wax\Shop\Events\OrderPlacedEvent;

class InventoryListener
{
    public function handle(OrderPlacedEvent $event)
    {
        $event->order()->items->each(function ($item) {
            if ($item->modifier && !is_null($item->modifier->getAttributes()['inventory'])) {
                $item->modifier->inventory -= $item->quantity;
                $item->modifier->save();
            } else {
                $item->product->inventory -= $item->quantity;
                $item->product->save();
            }
        });
    }
}