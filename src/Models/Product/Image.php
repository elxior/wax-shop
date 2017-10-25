<?php

namespace App\Shop\Models\Product;

use Illuminate\Database\Eloquent\Model;
use Wax\Core\Eloquent\Traits\HasDynamicCasts;

class Image extends Model
{
    use HasDynamicCasts;

    protected $table = 'product_images';
    protected $casts = ['image' => 'image'];

    protected $visible = ['image', 'default', 'caption'];

    public $timestamps = false;

}
