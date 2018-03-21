<?php

namespace Wax\Shop\Models\Product;

use Illuminate\Database\Eloquent\Model;
use Wax\Shop\Models\Product;

class Customization extends Model
{
    protected $table = 'product_customization';

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
