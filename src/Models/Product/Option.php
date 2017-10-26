<?php

namespace Wax\Shop\Models\Product;

use Illuminate\Database\Eloquent\Model;

class Option extends Model
{
    protected $table = 'product_options';
    protected $with = ['values'];

    public function values()
    {
        return $this->hasMany(OptionValue::class);
    }
}
