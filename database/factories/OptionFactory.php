<?php


$factory->define(Wax\Shop\Models\Product\Option::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->unique()->word()
    ];
});

$factory->define(Wax\Shop\Models\Product\OptionValue::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->unique()->word(),

        /**
         * This gets replaced when the value is attached to the object, but it is not a nullable field so it
         * needs a fake value (e.g. 0) in order to create the value.
         */
        'option_id' => 0,
    ];
});
