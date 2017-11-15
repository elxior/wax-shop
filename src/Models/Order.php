<?php

namespace Wax\Shop\Models;

use Wax\Core\Support\Localization\Currency;
use Wax\Shop\Events\OrderChanged\CouponChangedEvent;
use Wax\Shop\Events\OrderPlacedEvent;
use Wax\Shop\Models\Order\Item;
use Wax\Shop\Models\Order\Bundle as OrderBundle;
use Wax\Shop\Models\Order\Coupon as OrderCoupon;
use Wax\Shop\Models\Order\Payment;
use Wax\Shop\Models\Order\Shipment;
use Wax\Shop\Validators\OrderItemQuantityValidator;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

/**
 * @property Collection|OrderCoupon[] $bundles Bundle discounts applied to the order.
 * @property OrderCoupon|null $coupon The coupon applied to the order.
 * @property Collection|Shipment[] $shipments All shipments associated with the order.
 * @property Shipment|null $default_shipment Shortcut to a primary shipment.
 * @property Collection|Payment[] $payments All payments applied to the order.
 * @property Collection|Item[] $items The items/products contained in this order, i.e. the "cart"
 *
 * @property Carbon/Carbon $placed_at
 * @property Carbon/Carbon $processed_at
 * @property Carbon/Carbon $shipped_at
 * @property Carbon/Carbon $archived_at
 *
 * @property string $session_id Orders belonging to unauthenticated users will have a session_id
 * @property int $user_id Orders belonging to authenticated users will have a user_id
 *
 * @property int $item_count Number of distinct products.
 * @property int $total_quantity Sum of all item quantities in the order.
 * @property float $discountable_total Total price of all discountable items in the cart. Used for calculating the
 *      minimum order threshold for coupons.
 * @property float $item_gross_subtotal Total of all line item prices. sum(price * quantity)
 * @property float $item_discount_amount Total coupon value applied to the cart items.
 * @property float $item_subtotal Total of all line item prices after coupons are applied.
  *
 * @property float $flat_shipping_subtotal Total of all flat shipping charges.
 * @property float $shipping_service_subtotal Total of all carrier shipping rates.
 * @property float $shipping_gross_subtotal Sum of all flat-shipping and carrier rates for the cart.
 * @property float $shipping_discount_amount Total coupon value applied to shipping charges.
 * @property float $shipping_subtotal Sum of shipping charges after coupons are applied.
 *
 * @property float $tax_subtotal Total of a shipment tax amounts.
 * @property float $gross_total Total price for the order. Cart subtotal + shipping + tax.
 * @property float $coupon_value Total value of coupons applied to the order.
 * @property float $bundle_value Total value of bundle discounts applied to the order.
 * @property float $discount_amount Combined total discount for coupons and bundles
 * @property float $total Total price for the order after coupons are applied.
 *
 * @property float $payment_total Total amount of applied payments (can be Authorized or Captured)
 * @property float $balance_due Order total minus applied payments
 *
 * @method Builder|Order mine scope for orders belonging to the current user
 * @method Builder|Order active scope for an order that has not yet been placed
 * @method Builder|Order placed scope for completed orders
 * @method Builder|Order processed scope for orders that have been processed (payment captured or sent to fulfillment)
 * @method Builder|Order shipped scope for orders that have been shipped
 * @method Builder|Order archived scope for orders that were archived in the admin control panel
 */
class Order extends Model
{
    protected $table = 'orders';
    protected $with = ['shipments', 'payments', 'coupon', 'bundles'];
    protected $appends = [
        'default_shipment',
        'items',

        'item_count',
        'total_quantity',

        'item_gross_subtotal', 'item_discount_amount', 'item_subtotal',

        'flat_shipping_subtotal', 'shipping_service_subtotal',
        'shipping_gross_subtotal', 'shipping_discount_amount', 'shipping_subtotal',

        'tax_subtotal',
        'gross_total',
        'coupon_value',
        'bundle_value',
        'discount_amount',
        'total',
        'balance_due',
    ];

    protected $hidden = [
        'shipments',
        'session_id',
        'user_id',
        'searchIndex',
    ];

    protected $casts = [
        'placed_at' => 'timestamp',
        'processed_at' => 'timestamp',
        'shipped_at' => 'timestamp',
        'archived_at' => 'timestamp',
    ];


    public function scopeMine(Builder $query)
    {
        if (Auth::check()) {
            return $query->where('user_id', Auth::user()->id);
        }

        return $query->where('session_id', Session::getId());
    }

    public function scopeActive(Builder $query)
    {
        return $query->whereNull('placed_at');
    }

    public function scopePlaced(Builder $query)
    {
        return $query->whereNotNull('placed_at');
    }

    public function scopeProcessed(Builder $query)
    {
        return $query->whereNotNull('processed_at');
    }

    public function scopeShipped(Builder $query)
    {
        return $query->whereNotNull('shipped_at');
    }

    public function scopeArchived(Builder $query)
    {
        return $query->whereNotNull('archived_at');
    }

    public function bundles()
    {
        return $this->hasMany(OrderBundle::class);
    }

    public function coupon()
    {
        return $this->hasOne(OrderCoupon::class);
    }

    public function shipments()
    {
        return $this->hasMany(Shipment::class)->orderBy('id', 'asc');
    }

    public function hasProduct(int $productId, array $options = null, array $customizations = null) : bool
    {
        return $this->shipments
            ->filter
            ->findItem($productId, $options, $customizations)
            ->isNotEmpty();
    }

    public function getDefaultShipmentAttribute()
    {
        return $this->shipments()->firstOrCreate([]);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function getItemsAttribute()
    {
        return $this->shipments->pluck('items')->flatten(1);
    }

    public function getItemCountAttribute()
    {
        return (int)$this->shipments->sum('item_count');
    }

    public function getTotalQuantityAttribute()
    {
        return $this->shipments->sum('total_quantity');
    }

    public function getDiscountableTotalAttribute()
    {
        return Currency::round($this->shipments->sum('discountable_total'));
    }

    public function getItemGrossSubtotalAttribute()
    {
        return Currency::round($this->shipments->sum('item_gross_subtotal'));
    }

    public function getItemSubtotalAttribute()
    {
        return Currency::round($this->shipments->sum('item_subtotal'));
    }

    public function getItemDiscountAmountAttribute()
    {
        return Currency::round($this->shipments->sum('item_discount_amount'));
    }

    public function getFlatShippingSubtotalAttribute()
    {
        return Currency::round($this->shipments->sum('flat_shipping_subtotal'));
    }

    public function getShippingServiceSubtotalAttribute()
    {
        return Currency::round($this->shipments->sum('shipping_service_amount'));
    }

    public function getShippingGrossSubtotalAttribute()
    {
        return Currency::round($this->shipments->sum('shipping_gross_subtotal'));
    }

    public function getShippingDiscountAmountAttribute()
    {
        return Currency::round($this->shipments->sum('shipping_discount_amount'));
    }

    public function getShippingSubtotalAttribute()
    {
        return Currency::round($this->shipments->sum('shipping_subtotal'));
    }

    public function getTaxSubtotalAttribute()
    {
        return Currency::round($this->shipments->sum('tax_amount'));
    }

    public function getCouponValueAttribute()
    {
        return Currency::round($this->coupon->calculated_value ?? 0);
    }

    public function getBundleValueAttribute()
    {
        return Currency::round($this->bundles->sum('calculated_value'));
    }

    public function getDiscountAmountAttribute()
    {
        return Currency::round($this->coupon_value + $this->bundle_value);
    }

    public function getGrossTotalAttribute()
    {
        return Currency::round($this->shipments->sum('gross_total'));
    }

    public function getTotalAttribute()
    {
        return Currency::round($this->shipments->sum('total'));
    }

    public function getPaymentTotalAttribute()
    {
        return Currency::round($this->payments()->authorized()->sum('amount'));
    }

    public function getBalanceDueAttribute()
    {
        return Currency::round($this->total - $this->payment_total);
    }

    public function calculateTax()
    {
        $this->shipments->each->calculateTax();
    }

    public function invalidateTax()
    {
        $this->shipments->each->invalidateTax();
    }

    public function invalidateShipping()
    {
        $this->shipments->each->invalidateShipping();
    }

    public function commitTax()
    {
        $this->shipments->each->commitTax();
    }

    public function applyCoupon(string $code) : bool
    {
        $coupon = Coupon::where('code', $code)->first();
        if (!$coupon) {
            return false;
        }

        if (!$coupon->validate($this)) {
            return false;
        }

        if ($this->coupon) {
            $this->coupon->delete();
        }

        $this->coupon()->create([
            'title' => $coupon->title,
            'code' => $coupon->code,
            'expired_at' => $coupon->expired_at,
            'dollars' => $coupon->dollars,
            'percent' => $coupon->percent,
            'minimum_order' => $coupon->minimum_order,
            'one_time' => $coupon->one_time,
            'include_shipping' => $coupon->include_shipping,
        ]);

        $this->calculateDiscounts();

        return true;
    }

    public function removeCoupon()
    {
        if (!$this->coupon) {
            return;
        }

        $this->coupon->delete();

        $this->calculateDiscounts();
    }

    public function calculateDiscounts()
    {
        // make sure coupon / bundle relations are up to date
        $this->refresh();

        $this->resetDiscounts();

        if ($this->coupon) {
            $this->coupon->calculateValue();
            $this->refresh();
        }

        $this->applyBundleDiscounts();

        $this->refresh();
        event(new CouponChangedEvent($this));
    }

    protected function applyBundleDiscounts()
    {
        $orderProductIds = $this->items->pluck('product_id');

        $bundles = Bundle::whereHas('products', function ($query) use ($orderProductIds) {
            $query->whereIn('products.id', $orderProductIds);
        })->orderBy('percent', 'desc')->get();

        $bundles->filter(function ($bundle) use ($orderProductIds) {
            return $bundle->products->count() == $orderProductIds->intersect($bundle->products->pluck('id'))->count();
        })->each(function ($bundle) {
            $items = $this->items
                ->where('discount_amount', 0)
                ->wherein('product_id', $bundle->products->pluck('id'));

            if ($items->isEmpty()) {
                return;
            }

            $orderBundle = $this->bundles()->create([
                'name' => $bundle->name,
                'percent' => $bundle->percent,
            ]);
            $orderBundle->items()->saveMany($items);

            $items->each(function ($item) use ($orderBundle) {
                $item->discount_amount = round($item->gross_subtotal * $orderBundle->percent / 100, 2);
                $item->bundle_id = $orderBundle->id;
                $item->save();
            });

            $orderBundle->refresh();
            $orderBundle->calculated_value = $orderBundle->items->sum('discount_amount');
            $orderBundle->save();

            $this->refresh();
        });
    }

    protected function resetDiscounts()
    {
        // trigger individual deletes so the 'deleting' event is caught
        $this->bundles->each->delete();

        $this->shipments->each(function ($shipment) {
            $shipment->shipping_discount_amount = null;
            $shipment->save();
        });

        $this->items->each(function ($item) {
            $item->discountable = null;
            $item->discount_amount = null;
            $item->bundle_id = null;
            $item->save();
        });

        if ($this->coupon) {
            $this->coupon->calculated_value = null;
            $this->coupon->save();
        }

        $this->refresh();
    }

    public function validateTax() : bool
    {
        if (!$this->shipments->count()) {
            return false;
        }

        return $this->shipments->reject->validateTax()->isEmpty();
    }

    public function validateShipping() : bool
    {
        if (!$this->shipments->count()) {
            return false;
        }

        return $this->shipments->reject->validateShipping()->isEmpty();
    }

    /**
     * Check if there are items associated with every shipment
     *
     * @return bool
     */
    public function validateHasItems() : bool
    {
        if (!$this->shipments->count()) {
            return false;
        }

        return $this->shipments->reject(function ($shipment) {
            return $shipment->items->count() > 0;
        })->isEmpty();
    }

    public function validateInventory() : bool
    {
        return $this->items->reject(function ($item) {
            return (new OrderItemQuantityValidator($item->id, $item->quantity))
                ->passes();
        })->isEmpty();
    }

    /**
     * Test that all the conditions have been met for a payment to be made on the order.
     *
     * @return bool
     */
    public function validatePayable() : bool
    {
        return $this->validateHasItems()
            && $this->validateInventory()
            && $this->validateShipping()
            && $this->validateTax();
    }

    /**
     * Tests that the order is ready to be `placed`. The tests are similar to `Payable`, except:
     *   - Skip the inventory check. Any change in inventory after the payment was made shouldn't stop the order.
     *   - Balance Due must be Zero.
     *
     * @return bool
     */
    public function validatePlaceable() : bool
    {
        return $this->validateHasItems()
            && $this->validateShipping()
            && $this->validateTax()
            && ($this->balance_due == 0);
    }

    /**
     * Place the order, i.e. persist all the ephemeral order data (product details, etc), set the `placed_at`
     * flag, and trigger an OrderPlacedEvent event.
     *
     * @return bool
     */
    public function place() : bool
    {
        $this->refresh();

        if (!$this->validatePlaceable()) {
            return false;
        }

        $this->persistItemData();
        $this->persistShipmentMetadata();
        $this->persistOrderMetadata();

        event(new OrderPlacedEvent($this->fresh()));

        return true;
    }

    protected function persistItemData()
    {
        // Set the attributes on the Order Item to match the underlying Product data via the magic getters
        $this->items->each(function ($item) {
            $item->sku = $item->getAttribute('sku');
            $item->name = $item->getAttribute('name');
            $item->price = $item->getAttribute('price');
            $item->shipping_flat_rate = $item->getAttribute('shipping_flat_rate');
            $item->shipping_enable_rate_lookup = $item->getAttribute('shipping_enable_rate_lookup');
            $item->shipping_disable_free_shipping = $item->getAttribute('shipping_disable_free_shipping');
            $item->dim_l = $item->getAttribute('dim_l');
            $item->dim_w = $item->getAttribute('dim_w');
            $item->dim_h = $item->getAttribute('dim_h');
            $item->weight = $item->getAttribute('weight');
            $item->one_per_user = $item->getAttribute('one_per_user');
            $item->taxable = $item->getAttribute('taxable');
            $item->discountable = $item->getAttribute('discountable');

            $item->save();
        });
    }

    protected function persistShipmentMetadata()
    {
        $this->shipments->each(function ($shipment) {
            $shipment->sequence = Shipment::max('sequence') + 1;
            $shipment->save();
        });
    }

    protected function persistOrderMetaData()
    {
        if (empty($this->email)) {
            if (Auth::check()) {
                $this->email = Auth::user()->email;
            } elseif (!empty($this->payments->first()->email)) {
                $this->email = $this->payments->first()->email;
            } elseif (!empty($this->shipments->first()->email)) {
                $this->email = $this->shipments->first()->email;
            }
        }

        // set the actual total from the calculated attribute getter
        $this->total = $this->getAttribute('total');

        if (empty($this->sequence)) {
            $this->sequence = Order::max('sequence') + 1;
        }

        $this->ip_address = request()->ip();

        $this->placed_at = Carbon::now();

        $this->searchIndex = $this->toJson();

        $this->save();
    }
}
