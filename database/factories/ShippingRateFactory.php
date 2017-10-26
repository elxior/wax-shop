<?php


$factory->define(\Wax\Shop\Models\Order\ShippingRate::class, function (Faker\Generator $faker) {
    return [
        'shipment_id' => 0,
        'carrier' => $faker->company(),
        'service_code' => $faker->bothify('??#'),
        'service_name' => $faker->words(2, true),
        'business_transit_days' => $faker->randomDigit(),
        'amount' => $faker->randomFloat(2, 3, 20),
        'box_count' => 1,
        'packaging' => '',
    ];
});
