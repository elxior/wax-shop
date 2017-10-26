<?php

namespace Wax\Shop\Models\Order;

use Illuminate\Database\Eloquent\Model;

class ItemOption extends Model
{
    protected $table = 'order_item_options';
    protected $fillable = [
        'option_id',
        'value_id',
    ];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}
