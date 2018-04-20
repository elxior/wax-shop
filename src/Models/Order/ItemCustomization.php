<?php

namespace Wax\Shop\Models\Order;

use Illuminate\Database\Eloquent\Model;

class ItemCustomization extends Model
{
    protected $table = 'order_item_customizations';
    protected $guarded = [];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}
