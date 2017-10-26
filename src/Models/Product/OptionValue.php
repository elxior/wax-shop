<?php

namespace Wax\Shop\Models\Product;

use Illuminate\Database\Eloquent\Model;

class OptionValue extends Model
{
    protected $table = 'product_option_values';

    public function option()
    {
        return $this->belongsTo(Option::class);
    }
}
