<?php


$factory->define(Wax\Shop\Models\Product\Customization::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->unique()->word()
    ];
});
