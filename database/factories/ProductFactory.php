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
    ];
});

$factory->state(Wax\Shop\Models\Product::class, 'one_per_user', function () {
    return [
        'one_per_user' => 1,
    ] ;
});
