<?php

namespace Wax\Shop\Filters;

use Wax\Shop\Models\Product;
use Wax\Shop\Models\Product\Category;
use Wax\Core\Filters\FilterRelation;

class CategoryFilter extends FilterRelation
{
    protected $model = Category::class;
    protected $baseModel = Product::class;
    protected $relation = 'category';
    protected $inverseRelation = 'products';
    protected $whereColumn = 'url_slug';
    protected $labelColumn = 'name';
    protected $name = 'category';
}
