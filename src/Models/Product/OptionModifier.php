<?php

namespace Wax\Shop\Models\Product;

use Wax\Shop\Models\Product;
use Illuminate\Database\Eloquent\Model;

/**
 * An OptionModifier is a distinct SKU created by a combination of product options, e.g. size+color. It can override
 * price, inventory, and shipping weight of the base product.
 *
 * @property Product $product The Product associated with this modifier.
 * @property float $price Unit price of the product.
 * @property string $sku SKU number of the item.
 * @property int $inventory Available inventory for the item.
 * @property float $weight Shipping weight of the item. Unit of measure (Lbs./Oz.) depends on shop config.
 */
class OptionModifier extends Model
{
    protected $table = 'product_option_modifiers';

    protected $fillable = [
        'product_id',
        'values',
        'sku',
        'price',
        'inventory',
        'weight',
        'disable',
    ];

    public function product()
    {
        return $this->belongsTo(config('wax.shop.models.product'));
    }

    public function getInventoryAttribute($value)
    {
        return $value ?? $this->product->inventory;
    }

    public function getEffectiveInventoryAttribute()
    {
        return min(
            config('wax.shop.inventory.max_cart_quantity'),
            (config('wax.shop.inventory.track')) ? $this->inventory : PHP_INT_MAX
        );
    }
}
