<?php

namespace Wax\Shop\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $name
 * @property int $percent
 * @property Collection|Product[] $products
 */
class Bundle extends Model
{
    protected $table = 'product_bundles';
    protected $fillable = [
        'name',
        'percent',
        'products',
    ];
    protected $with = [
        'products'
    ];

    public function products()
    {
        return $this->belongsToMany(config('wax.shop.models.product'), 'product_bundle_links');
    }
}
