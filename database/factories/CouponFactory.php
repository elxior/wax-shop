<?php

use Carbon\Carbon;

$factory->define(\Wax\Shop\Models\Coupon::class, function (Faker\Generator $faker) {
    return [
        'title' => $faker->catchPhrase(),
        'code' => $faker->bothify('????####'),
        'expired_at' => Carbon::tomorrow(),
        'dollars' => 0,
        'minimum_order' => 0,
        'one_time' => false,
        'include_shipping' => false,
    ];
});
