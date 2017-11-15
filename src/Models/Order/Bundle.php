<?php

namespace Wax\Shop\Models\Order;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $name
 * @property int $percent
 * @property Collection|Item[] $items
 */
class Bundle extends Model
{
    protected $table = 'order_bundles';
    protected $fillable = [
        'name',
        'percent',
        'items',
    ];

    public function items()
    {
        return $this->belongsToMany(Item::class, 'order_item_bundle_links');
    }
}
