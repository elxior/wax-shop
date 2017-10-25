<?php

namespace App\Shop\Models\Product;

use Illuminate\Database\Eloquent\Model;
use App\Shop\Models\Product;
use Wax\Core\Eloquent\Traits\HasDynamicCasts;

class Category extends Model
{
    use HasDynamicCasts;

    protected $table = 'product_categories';
    protected $casts = ['image' => 'image'];
    protected $visible = [
        'id',
        'name',
        'breadcrumb',
        'short_description',
        'image',
        'url',
    ];
    protected $appends = [
        'url',
    ];

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function getUrlAttribute()
    {
        return route('shop::catalogIndex', ['category' => $this->url_slug]);
    }

    public function regenerateBreadcrumbs()
    {
        $breadcrumbs = collect();

        $branchId = $this->id;
        while ($branchId > 0 && !is_null($cat = Category::find($branchId))) {
            $breadcrumbs->prepend($cat->name);
            $branchId = $cat->parent_id;
        }
        $this->breadcrumb = $breadcrumbs->implode(' > ');
        $this->save();
    }
}
