<?php

namespace Wax\Shop\Models\Order;

use Wax\Core\Eloquent\Traits\HasDynamicCasts;
use Wax\Shop\Models\Product;
use Wax\Shop\Models\Product\OptionModifier;
use Illuminate\Database\Eloquent\Model;

/**
 * This represents the products attached to a shipment, i.e. the "cart".
 *
 * @property Product $product The product associated with the cart item.
 * @property Shipment $shipment The shipment which contains this item.
 * @property \Wax\Shop\Models\Bundle[] $bundle The AVAILABLE bundles for this product. (not the active bundles)
 * @property ItemCustomization[] $customizations User provided customization, such as a gift card note or monogram.
 * @property ItemOption[] $options The selected options for the item, e.g. color.
 * @property OptionModifier|null $modifier A combination of options can have an associated
 *      Modifier, e.g. size+color can create a distinct sku and override the price/inventory/weight attributes.
 * @property int $quantity How many of the item were added to the shipment.
 * @property float $gross_unit_price Unit price of the item.
 * @property float $unit_price Unit price after coupons.
 * @property string $sku SKU number of the item.
 * @property int $inventory Available inventory for the item.
 * @property float $weight Shipping weight of the item. Unit of measure (Lbs./Oz.) depends on shop config.
 * @property float $gross_subtotal The item's unit price multiplied by quantity added to the shipment.
 * @property float $subtotal The item's base subtotal after coupon value is applied.
 * @property float $discount_amount The portion of a coupon applied to this line item.
 * @property float $shipping_flat_rate Flat-rate shipping price per unit.
 * @property float $shipping_flat_rate_subtotal The item's flat-rate shipping price multiplied by the quantity added
 *      to the shipment.
 */
class Item extends Model
{
    protected $table = 'order_items';
    protected $fillable = [
        'product_id',
        'quantity',
    ];

    protected $with = [
        'options',
        'product.bundles.products'
    ];

    protected $hidden = [
        'product',
        'price',
        'weight',
        'dim_l', 'dim_w', 'dim_h',
        'one_per_user',
        'taxable',
        'discountable',

        'shipping_flat_rate',
        'shipping_enable_rate_lookup',
        'shipping_disable_free_shipping',
        'shipping_enable_tracking_number',
    ];

    protected $appends = [
        'brand',
        'name',

        'gross_unit_price',
        'unit_price',
        'gross_subtotal',
        'subtotal',

        'image',
        'short_description',
        'url',
        'category',
        'bundles',
    ];

    public function product()
    {
        return $this->belongsTo(config('wax.shop.models.product'));
    }

    public function getBundlesAttribute()
    {
        return $this->product->bundles;
    }

    public function shipment()
    {
        return $this->belongsTo(Shipment::class);
    }

    public function customizations()
    {
        return $this->hasMany(ItemCustomization::class);
    }

    public function options()
    {
        return $this->hasMany(ItemOption::class);
    }

    public function getModifierAttribute() : ?OptionModifier
    {
        if ($this->product->optionModifiers->isEmpty()) {
            return null;
        }

        $valueString = $this->options->pluck('value_id')->sort()->implode('-');

        return $this->product->optionModifiers
            ->where('values', $valueString)
            ->first();
    }

    public function getGrossUnitPriceAttribute() : float
    {
        return  $this->modifier->price ?? $this->price ?? $this->product->price;
    }

    public function getUnitPriceAttribute() : float
    {
        return $this->subtotal / $this->quantity;
    }

    public function getNameAttribute($value) : string
    {
        return $value ?? $this->product->name;
    }

    public function getBrandAttribute($value) : ?string
    {
        return $value ?? $this->product->brand->name ?? null;
    }

    public function getSkuAttribute($value) : string
    {
        return $this->modifier->sku ?? $value ?? $this->product->sku;
    }

    public function getInventoryAttribute($value) : int
    {
        return $this->modifier->inventory ?? $value ?? $this->product->inventory;
    }

    public function getWeightAttribute($value) : float
    {
        return $this->modifier->weight ?? $value ?? $this->product->weight;
    }

    public function getGrossSubtotalAttribute() : float
    {
        return $this->gross_unit_price * $this->quantity;
    }

    public function getSubtotalAttribute()
    {
        return $this->gross_subtotal - $this->discount_amount;
    }

    public function getFlatShippingSubtotalAttribute() : float
    {
        return $this->shipping_flat_rate * $this->quantity;
    }

    public function getImageAttribute() : ?Product\Image
    {
        return $this->product->default_image ?? null;
    }

    public function getShortDescriptionAttribute($value) : string
    {
        return $value ?? $this->product->short_description;
    }

    public function getUrlAttribute() : ?string
    {
        return $this->product->url ?? null;
    }

    public function getCategoryAttribute() : ?Product\Category
    {
        return $this->product->category ?? null;
    }

    /**
     * Override the parent getAttribute method - if a property is not defined (null) on the
     * Order Item, get the value from the the associated product.
     *
     * @param string $key
     * @return mixed
     */
    public function getAttribute($key)
    {
        $value = parent::getAttribute($key);
        if (!is_null($value)) {
            return $value;
        }

        return $this->product->getAttribute($key);
    }
}
