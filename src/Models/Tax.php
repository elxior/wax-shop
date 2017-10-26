<?php

namespace App\Shop\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string $zone
 * @property float $rate
 * @property bool $tax_shipping
 */
class Tax extends Model
{
    protected $table = 'tax';
    protected $fillable = [
        'zone',
        'rate',
        'tax_shipping',
    ];
}
