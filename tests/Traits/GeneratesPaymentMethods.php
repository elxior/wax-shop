<?php

namespace Tests\Shop\Traits;

use Faker\Factory;

trait GeneratesPaymentMethods
{
    protected $faker;

    protected function generatePaymentMethodData()
    {
        if (!$this->faker) {
            $this->faker = Factory::create();
        }
        return [
            'cardNumber' => $this->faker->creditCardNumber(),
            'expMonth' => $this->faker->numberBetween(1, 12),
            'expYear' => $this->faker->numberBetween(date('y')+1, date('y')+10),
            'cvc' => $this->faker->numberBetween(100, 999),
            'firstName' => $this->faker->firstName(),
            'lastName' => $this->faker->lastName,
            'address' => $this->faker->streetAddress,
            'zip' => $this->faker->postcode,
        ];
    }
}
