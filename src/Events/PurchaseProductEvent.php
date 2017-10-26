<?php

namespace Wax\Shop\Events;

use Wax\Shop\Models\Product;

class PurchaseProductEvent
{
    public function __construct(Product $product)
    {
        $this->product = $product;
    }
}
