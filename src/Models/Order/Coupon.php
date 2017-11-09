<?php

namespace Wax\Shop\Models\Order;

use Wax\Core\Eloquent\Traits\HasDynamicCasts;
use Wax\Shop\Models\Order;
use Wax\Shop\Traits\ValidatesCoupons;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $title
 * @property \Carbon\Carbon $expired_at
 * @property float $dollars
 * @property int $percent
 * @property float $minimum_order
 * @property string $code
 * @property boolean $one_time
 * @property boolean $include_shipping
 *
 * @property float $calculated_value
 *
 */
class Coupon extends Model
{
    use ValidatesCoupons,
        HasDynamicCasts;

    protected $table = 'order_coupons';
    protected $fillable = [
        'title',
        'code',
        'expired_at',
        'dollars',
        'percent',
        'minimum_order',
        'one_time',
        'include_shipping',
    ];
    protected $casts = [
        'expired_at' => 'datetime',
        'calculated_value' => 'currency',
    ];
    protected $hidden = [
        'order',
    ];

    protected static function boot()
    {
        parent::boot();
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function validate()
    {
        return $this->validateCouponForOrder($this, $this->order);
    }

    protected function calculateBaseValue()
    {
        return min(
            $this->order->discountable_total,
            $this->dollars + round($this->order->discountable_total * ($this->percent / 100), 2)
        );
    }

    public function calculateValue()
    {
        $couponValue = $this->calculateBaseValue();

        $this->calculated_value = $couponValue;

        $this->distributeCouponValueToCart($couponValue);
        $this->applyShippingDiscount();

        $this->save();
    }

    protected function distributeCouponValueToCart($couponValue)
    {
        $items = $this->order->items;

        foreach ($items as $item) {
            if ($item->discountable && ($item->discount_amount == 0)) {
                $ratio = $item->gross_subtotal / $this->order->discountable_total;
                $item->discount_amount = min($couponValue, round($ratio * $this->calculated_value, 2));
                $couponValue -= $item->discount_amount;
            }
            $item->save();
        }

        if ($couponValue === 0) {
            return;
        }

        // if there is any unapplied value left due to rounding (like a penny maybe),
        // apply it the first item that's discountable
        $couponValue = round($couponValue, 2);

        foreach ($items as $item) {
            if ($item->discountable) {
                $addl = min($couponValue, $item->subtotal);
                $item->discount_amount += $addl;
                $couponValue -= $addl;
                if ($addl != 0) {
                    $item->save();
                }
            }
            if ($couponValue === 0) {
                break;
            }
        }
    }


    protected function applyShippingDiscount()
    {
        if (!$this->include_shipping) {
            return false;
        }

        $this->order->shipments->each(function ($shipment) {
            $shipmentDiscount = round($shipment->shipping_subtotal * ($this->percent / 100), 2);
            $shipment->shipping_discount_amount = $shipmentDiscount;
            $shipment->save();

            $this->calculated_value += $shipmentDiscount;
        });
        $this->save();
    }
}
