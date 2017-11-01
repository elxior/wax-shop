<?php

namespace Wax\Shop\Observers;

use Wax\Shop\Models\Order\Bundle;

class OrderBundleObserver
{
    public function deleting(Bundle $bundle)
    {
        $bundle->items()->detach();
    }
}
