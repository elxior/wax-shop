<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(\Tests\Shop\Support\Models\User::class, function (Faker\Generator $faker) {
    static $password;

    return [
        'firstname' => $faker->firstName(),
        'lastname' => $faker->lastName(),
        'email' => $faker->unique()->safeEmail,
        'password' => $password ?: $password = bcrypt('secret'),
        'remember_token' => str_random(10),
    ];
});
