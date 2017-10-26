<?php

use Carbon\Carbon;

$factory->define(\Wax\Shop\Models\Order\Payment::class, function (Faker\Generator $faker) {
    return [
        'order_id' => 0,

        'authorized_at' => Carbon::now(),
        'captured_at' => null,

        'amount' => 0,
        'type' => 'Cash',
        'account' => $faker->numerify('xxxx xxxx xxxx ####'),
        'error' => '',
        'response' => 'AUTHORIZED',
        'auth_code' => $faker->randomNumber(7),
        'transaction_ref' => $faker->regexify('[A-Z0-9]{12}'),

        'firstname' => $faker->firstName,
        'lastname' => $faker->lastName,
        'company' => $faker->company,
        'email' => $faker->safeEmail,
        'phone' => $faker->phoneNumber,
        'address1' => $faker->streetAddress,
        'address2' => $faker->secondaryAddress,
        'city' => $faker->city,
        'state' => $faker->stateAbbr,
        'zip' => $faker->postcode,
        'country' => $faker->countryCode
    ];
});
