<?php

namespace Wax\Shop\Models\Product;

use Illuminate\Database\Eloquent\Model;

class Attribute extends Model
{

    protected $table = 'product_attribute_links';
    protected $with = ['nameRelation', 'valueRelation'];
    protected $hidden = ['product_id', 'cms_sort_id', 'nameRelation', 'valueRelation'];
    protected $appends = ['name', 'value'];

    public function nameRelation()
    {
        return $this->belongsTo('Wax\Shop\Models\Product\AttributeName', 'name_id');
    }

    public function getNameAttribute()
    {
        return $this->nameRelation->name;
    }

    public function valueRelation()
    {
        return $this->belongsTo('Wax\Shop\Models\Product\AttributeValue', 'value_id');
    }

    public function getValueAttribute()
    {
        return $this->valueRelation->value;
    }
}
