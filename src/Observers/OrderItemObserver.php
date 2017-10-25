<?php

namespace App\Shop\Observers;

use App\Shop\Models\Order\Item;

class OrderItemObserver
{
    public function deleting(Item $item)
    {
        $item->options->each(function ($option) {
            $option->delete();
        });

        $item->customizations->each(function ($child) {
            $child->delete();
        });
    }
}