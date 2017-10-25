<?php

namespace App\Shop\Filters;

use App\Shop\Models\Product;
use App\Shop\Models\Product\Category;
use Illuminate\Database\Eloquent\Model;
use Wax\Core\Filters\Filter;
use Wax\Core\Filters\FilterOption;

class CategoryFilter extends Filter
{
    protected $model = Category::class;
    protected $baseModel = Product::class;
    protected $relation = 'category';
    protected $inverseRelation = 'articles';
    protected $comparisonColumn = 'url_slug';
    protected $name = 'category';

    public function accepts(Model $model)
    {
        return $model->categories ? $model->categories->pluck('url_slug')->contains($this->getValue()) : false;
    }

    public function extractModelLabel(Model $model)
    {
        return $model->name;
    }

    public function buildOption(Model $model)
    {
        $value = $this->extractModelValue($model);
        $label = $this->extractModelLabel($model);

        return new FilterOption($label, $value);
    }
}
