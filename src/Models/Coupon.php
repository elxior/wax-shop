<?php

namespace Wax\Shop\Models;

use Wax\Shop\Scopes\ExpiredScope;
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
 */
class Coupon extends Model
{
    use ValidatesCoupons;

    protected $table = 'coupons';
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
        'expired_at' => 'datetime'
    ];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new ExpiredScope);
    }

    public function validate(Order $order)
    {
        return $this->validateCouponForOrder($this, $order);
    }
}
