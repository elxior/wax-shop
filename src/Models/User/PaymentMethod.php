<?php

namespace Wax\Shop\Models\User;

use App\User;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $firstname
 * @property string $lastname
 * @property string $address
 * @property string $zip
 */
class PaymentMethod extends Model
{
    protected $guarded = [];
    protected $table = 'user_payment_methods';
    public $timestamps = false;
    protected $hidden = ['payment_profile_id', 'user_id', 'masked_card_number', 'expiration_date'];
    protected $appends = ['account_number', 'exp_month', 'exp_year'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getExpirationDateAttribute($value)
    {
        if (preg_match('#^(\d+)/(\d+)$#', $value, $m)) {
            return [
                'month' => $m[1],
                'year' => $m[2]
            ];
        }
        return ['month' => null, 'year' => null];
    }

    public function getAccountNumberAttribute()
    {
        return $this->masked_card_number;
    }

    public function getExpMonthAttribute()
    {
        return $this->expiration_date['month'];
    }

    public function getExpYearAttribute()
    {
        return $this->expiration_date['year'];
    }
}
