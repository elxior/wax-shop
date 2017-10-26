<?php

namespace Wax\Shop\Models\Order;

use Wax\Shop\Models\Order;
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
 * @method Builder|Payment authorized scope for approved/authorized payments
 * @method Builder|Payment captured scope for committed/captured payments
 */
class Payment extends Model
{
    protected $table = 'order_payments';

    public function order()
    {
        return $this->belongsTo(Order::class);
    }


    public function scopeAuthorized(Builder $query)
    {
        return $query->whereNotNull('authorized_at')
            ->whereIn('response', ['AUTHORIZED', 'CAPTURED']);
    }

    public function scopeCaptured(Builder $query)
    {
        return $query->whereNotNull('captured_at')
            ->where('response', 'CAPTURED');
    }
}
