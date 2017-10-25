<?php

namespace App\Shop\Models\Order;

use Illuminate\Database\Eloquent\Model;

class ItemCustomization extends Model
{
    protected $table = 'order_item_customizations';

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}