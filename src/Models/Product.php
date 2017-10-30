<?php

namespace Wax\Shop\Models;

//use App\Contracts\CanMakeEntitiesContract;
use App\Localization\Currency;
use Wax\Shop\Models\Product\Category;
use Wax\Shop\Models\Product\Image;
use Wax\Shop\Models\Product\Option;
use Wax\Shop\Models\Product\OptionModifier;
use Wax\Shop\Models\Product\OptionValue;
use App\Wax\Lang;
use Wax\Shop\Scopes\ActiveScope;
//use App\Traits\CanMakeEntities;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

/**
 * A Product is an item in the catalog that you can purchase. Duh.

 * @property Collection|OptionModifier[] $optionModifiers All available OptionModifiers for the product. A combination
 *      of options can have an associated Modifier, e.g. size+color can create a distinct sku and override the
 *      price/inventory/weight attributes.
 * @property Collection|Image[] $images
 * @property Image $default_image
 * @property Category $category
 * @property string $url
 * @property string $name The product name/title.
 * @property bool $active Is the property available for display in the catalog and for purchase.
 * @property string $short_description SEO/Meta description of the product
 * @property float $price Unit price of the item.
 * @property string $sku SKU number of the item.
 * @property int $inventory Available inventory for the item.
 * @property float $weight Shipping weight of the item. Unit of measure (Lbs./Oz.) depends on shop config.
 *
 */
class Product extends Model
    //implements CanMakeEntitiesContract
{
    //use CanMakeEntities;

    // model stuff
    protected $table = 'products';
    protected $with = [
        'category',
    ];
    protected $visible = [
        'id',
        'url',
        'sku',
        'keywords',
        'name',
        'description',
        'short_description',
        'formattedPrice',
        'featured',
        'category',
        'defaultImage',
    ];


    protected $appends = [
        'url',
        'defaultImage',
        'formattedPrice',
    ];

    public function __construct($attributes = array())
    {
        parent::__construct($attributes);
    }

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new ActiveScope);
    }


    public function getFormattedPriceAttribute()
    {
        return Currency::format($this->price);
    }

    public function scopeFeatured(Builder $query)
    {
        return $query->where('featured', '=', 1);
    }

    public function getUrlAttribute()
    {
        return route('shop::productDetail', ['slug' => $this->url_slug]);
    }

    public function images()
    {
        return $this->hasMany('Wax\Shop\Models\Product\Image')->orderBy('cms_sort_id');
    }

    public function getDefaultImageAttribute()
    {
        if (!$image = $this->images()->where('default', 1)->take(1)->first()) {
            return null;
        }
        if (empty($image['caption'])) {
            $image['caption'] = $this->name;
        }
        return $image;
    }

    public function category()
    {
        return $this->belongsTo('Wax\Shop\Models\Product\Category', 'category_id');
    }

    public function getEffectiveInventoryAttribute()
    {
        if (!$this->active) {
            return 0;
        }

        return min(
            config('wax.shop.inventory.max_cart_quantity'),
            (config('wax.shop.inventory.track')) ? $this->inventory : PHP_INT_MAX
        );
    }

    public function attrs()
    {
        return $this->hasMany('Wax\Shop\Models\Product\Attribute')->orderBy('cms_sort_id');
    }

    public function getMetaTitleAttribute()
    {
        $title = "{$this->sku} {$this->name} | {$this->short_description} | ".config('app.name');

        return Lang::trail($title, 255, false, false);
    }

    public function getRatingAttribute($value)
    {
        return round($value * (1 / config('wax.shop.ratings.increment'))) / (1 / config('wax.shop.ratings.increment'));
    }


    /**
     * Related products are similar or alternate product suggestions.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function relatedProducts()
    {
        return $this->belongsToMany(config('wax.shop.models.product'), 'product_related', 'product_id', 'related_id');
    }


    public function getOptionsAttribute()
    {
        $values = $this->optionValues;
        return $this->rawOptions->map(function ($option) use ($values) {
            $option->values = $values->where('option_id', $option->id)->keyBy('id');
            return $option;
        });
    }

    public function rawOptions()
    {
        return $this->belongsToMany(Option::class, 'product_option_links', 'product_id', 'option_id')
            ->withPivot('required');
    }

    public function optionValues()
    {
        return $this->belongsToMany(OptionValue::class, 'product_option_value_links', 'product_id', 'value_id')
            ->orderBy('value_id');
    }

    public function optionModifiers()
    {
        return $this->hasMany(OptionModifier::class);
    }

    /**
     * Create a multidimensional array of all possible combinations of product option values
     *
     * @param Collection $options the options node of a product model
     * @return Collection
     */
    public function getOptionPermutations($options = null)
    {
        if (is_null($options)) {
            $options = $this->options;
        }

        $option = $options->shift();
        $result = collect();

        if (!$option->pivot->required) {
            $option->values->prepend((object)['id'=>0, 'name'=>''], 0);
        }

        foreach ($option->values as $value) {
            $arr = (object)[
                'option_id' => $option->id,
                'option_name' => $option->name,
                'value_id' => $value->id,
                'value_name' => $value->name
            ];

            if (count($options)) {
                $flatOptions = $this->getOptionPermutations(clone($options));
                foreach ($flatOptions as $flatOption) {
                    $flatOption->prepend($arr);
                    $result->push($flatOption);
                }
            } else {
                $result->push(collect([$arr]));
            }
        }
        return $result;
    }
}
