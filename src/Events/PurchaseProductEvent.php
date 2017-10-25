<?php

namespace App\Shop\Events;

use App\Shop\Models\Product;

class PurchaseProductEvent
{
    public function __construct(Product $product)
    {
        $this->product = $product;
    }
}
