<?php

namespace Wax\Shop\Models\Order;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * @property \Carbon\Carbon $authorized_at
 * @property \Carbon\Carbon $captured_at
 * @property string $type (Credit Card, Gift Card, Cash, Purchase Order)
 * @property string $account
 * @property string $error
 * @property string $response (AUTHORIZED, CAPTURED, DECLINED, ERROR)
 * @property string $auth_code
 * @property string $transaction_ref
 * @property float $amount
 *
 * @property string $firstname
 * @property string $lastname
 * @property string $email
 * @property string $phone
 * @property string $company
 * @property string $address1
 * @property string $address2
 * @property string $city
 * @property string $state
 * @property string $zip
 * @property string $country Two-letter country code
 *
 * @method Builder|Payment approved scope for successfully authorized or captured payments
 * @method Builder|Payment authorized scope for authorized (not yet captured) payments
 * @method Builder|Payment captured scope for captured payments
 * @method Builder|Payment declined scope for declined/failed payments
 */
class Payment extends Model
{
    protected $table = 'order_payments';

    protected $guarded = [];

    protected $dates = [
        'created_at',
        'updated_at',
        'authorized_at',
    ];

    public function order()
    {
        return $this->belongsTo(config('wax.shop.models.order'));
    }

    public function scopeApproved(Builder $query)
    {
        return $query->whereNotNull('authorized_at')
            ->whereIn('response', ['AUTHORIZED', 'CAPTURED']);
    }

    public function scopeDeclined(Builder $query)
    {
        return $query->whereNotNull('authorized_at')
            ->whereNotIn('response', ['AUTHORIZED', 'CAPTURED']);
    }

    public function scopeAuthorized(Builder $query)
    {
        return $query
            ->whereNotNull('authorized_at')
            ->whereNull('captured_at')
            ->where('response', 'AUTHORIZED');
    }

    public function scopeCaptured(Builder $query)
    {
        return $query->whereNotNull('captured_at')
            ->where('response', 'CAPTURED');
    }
}
