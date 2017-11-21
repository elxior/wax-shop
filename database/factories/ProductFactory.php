<?php

$factory->define(Wax\Shop\Models\Product::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->catchPhrase(),
        'model' => $faker->numerify('product-####'),
        'description' => \Wax\Html::textToHtml($faker->paragraphs($faker->numberBetween(1, 4), true)),
        'short_description' => \Wax\Html::textToHtml($faker->paragraph()),
        'active' => 1,
        'price' => $faker->randomFloat(2, 10, 2000),
        'inventory' => $faker->numberBetween(10, 100),
        'sku' => $faker->ean8(),
        'keywords' => collect($faker->words($faker->numberBetween(3, 12)))->implode(', '),
        'taxable' => true,
        'shipping_flat_rate' => 0,
        'shipping_enable_rate_lookup' => false,
        'shipping_disable_free_shipping' => false,
        'shipping_enable_tracking_number' => true,
        'dim_l' => $faker->randomFloat(2, 1, 10),
        'dim_w' => $faker->randomFloat(2, 1, 10),
        'dim_h' => $faker->randomFloat(2, 1, 10),
        'weight' => $faker->randomFloat(2, 5, 20),
        'one_per_user' => false,
        'discountable' => true,
    ];
});

$factory->state(Wax\Shop\Models\Product::class, 'one_per_user', function () {
    return [
        'one_per_user' => 1,
    ] ;
});
