<?php

namespace App\Shop\Models;

use App\Shop\Events\OrderChanged\CouponChangedEvent;
use App\Shop\Models\Order\Item;
use App\Shop\Models\Order\Coupon as OrderCoupon;
use App\Shop\Models\Order\Payment;
use App\Shop\Models\Order\Shipment;
use App\Shop\Validators\OrderItemQuantityValidator;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

/**
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
    protected $with = ['shipments', 'payments', 'coupon'];
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
        'total',
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
        return $this->shipments->sum('item_count');
    }

    public function getTotalQuantityAttribute()
    {
        return $this->shipments->sum('total_quantity');
    }

    public function getDiscountableTotalAttribute()
    {
        return $this->shipments->sum('discountable_total');
    }

    public function getItemGrossSubtotalAttribute()
    {
        return $this->shipments->sum('item_gross_subtotal');
    }

    public function getItemSubtotalAttribute()
    {
        return $this->shipments->sum('item_subtotal');
    }

    public function getItemDiscountAmountAttribute()
    {
        return $this->shipments->sum('item_discount_amount');
    }

    public function getFlatShippingSubtotalAttribute()
    {
        return $this->shipments->sum('flat_shipping_subtotal');
    }

    public function getShippingServiceSubtotalAttribute()
    {
        return $this->shipments->sum('shipping_service_amount');
    }

    public function getShippingGrossSubtotalAttribute()
    {
        return $this->shipments->sum('shipping_gross_subtotal');
    }

    public function getShippingDiscountAmountAttribute()
    {
        return $this->shipments->sum('shipping_discount_amount');
    }

    public function getShippingSubtotalAttribute()
    {
        return $this->shipments->sum('shipping_subtotal');
    }

    public function getTaxSubtotalAttribute()
    {
        return $this->shipments->sum('tax_amount');
    }

    public function getCouponValueAttribute()
    {
        return $this->coupon->calculated_value ?? 0;
    }

    public function getGrossTotalAttribute()
    {
        return $this->shipments->sum('gross_total');
    }

    public function getTotalAttribute()
    {
        return $this->shipments->sum('total');
    }

    public function getPaymentTotalAttribute()
    {
        return $this->payments()->authorized()->sum('amount');
    }

    public function getBalanceDueAttribute()
    {
        return $this->total - $this->payment_total;
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
            $this->resetDiscounts();
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

        $this->refresh();
        $this->coupon->calculateValue();

        event(new CouponChangedEvent($this->fresh()));

        return true;
    }

    public function calculateCouponValue()
    {
        // make sure coupon relation is up to date
        $this->refresh();

        if ($this->coupon) {
            $this->coupon->calculateValue();
        } else {
            $this->resetDiscounts();
        }
    }

    public function resetDiscounts()
    {
        $this->shipments->each(function ($shipment) {
            $shipment->shipping_discount_amount = null;
            $shipment->save();
        });

        $this->items->each(function ($item) {
            $item->discount_amount = null;
            $item->save();
        });

        if ($this->coupon) {
            $this->coupon->calculated_value = null;
            $this->coupon->save();
        }
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

    public function validatePlaceable() : bool
    {
        return $this->validatePayable() && ($this->balance_due == 0);
    }
}
