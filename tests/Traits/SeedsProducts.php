<?php

namespace Tests\Shop\Traits;

use Wax\Shop\Models\Product;
use Wax\Shop\Models\Product\Option;
use Wax\Shop\Models\Product\OptionValue;
use Wax\Shop\Services\ShopService;

trait SeedsProducts
{
    /* @var Product[] $products */
    protected $products;

    public function testProductsExist()
    {
        $this->assertNotNull(Product::find(1));
        $this->assertNotNull(Product::find(2));
        $this->assertNotNull(Product::find(3));
        $this->assertNotNull(Product::find(4));


        $this->assertNull(Product::find(10));
    }

    protected function seedProducts()
    {
        $this->products['basic'] = factory(Product::class)
            ->create();

        $this->products['notDiscountable'] = factory(Product::class)
            ->create(['discountable' => 0]);

        $this->products['onePerUser'] = factory(Product::class)
            ->states('one_per_user')
            ->create();


        $options = collect(['Size', 'Color'])
            ->map(function ($optionName) {
                $option = factory(Option::class)
                    ->create(['name' => $optionName]);

                $option->values()
                    ->saveMany(factory(OptionValue::class, 3)->create());

                return $option;
            });

        $this->seedProductWithOptions($options);
        $this->seedProductOnePerUserWithOptions($options);
        $this->seedProductWithOptionModifiers($options);
    }

    protected function seedProductWithOptions($options)
    {
        $product = factory(Product::class)->create();
        $product->rawOptions()->attach($options->pluck('id'));
        $product->rawOptions->each(function ($option) use ($product) {
            $product->optionValues()->attach(
                $option->values->pluck('id')
            );
        });
        $this->products['withOptions'] = $product;
    }

    protected function seedProductOnePerUserWithOptions($options)
    {
        $product = factory(Product::class)
            ->states('one_per_user')
            ->create();
        $product->rawOptions()->attach($options->pluck('id'));
        $product->rawOptions->each(function ($option) use ($product) {
            $product->optionValues()->attach(
                $option->values->pluck('id')
            );
        });
        $this->products['onePerUserWithOptions'] = $product;
    }

    protected function seedProductWithOptionModifiers($options)
    {
        $product = factory(Product::class)->create();
        $product->rawOptions()->attach($options->pluck('id'));
        $product->rawOptions->each(function ($option) use ($product) {
            $product->optionValues()->attach(
                $option->values->pluck('id')
            );
        });
        $perms = $product->getOptionPermutations();
        $perms->each(function ($perm) use ($product) {
            $product->optionModifiers()->create([
                'values' => $perm->pluck('value_id')->sort()->implode('-'),
                'price' => $perm->sum('value_id') * 10,
                'sku' => $product->sku . '-' . $perm->pluck('value_id')->sort()->implode('-')
            ]);
        });
        $this->products['withOptionModifiers'] = $product;
    }
}
