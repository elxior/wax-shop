<?php

namespace Wax\Shop\Models\Order;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Wax\Core\Eloquent\Traits\HasDynamicCasts;

/**
 * @property string $name
 * @property int $percent
 * @property Collection|Item[] $items
 */
class Bundle extends Model
{
    use HasDynamicCasts;

    protected $table = 'order_bundles';
    protected $fillable = [
        'name',
        'percent',
        'items',
    ];
    protected $with = [
        //'items'
    ];
    protected $casts = [
        'calculated_value' => 'currency'
    ];

    public function items()
    {
        return $this->belongsToMany(Item::class, 'order_item_bundle_links');
    }
}
