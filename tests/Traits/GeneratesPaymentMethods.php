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
            'number' => $this->faker->creditCardNumber(),
            'expiryMonth' => $this->faker->numberBetween(1, 12),
            'expiryYear' => $this->faker->numberBetween(date('y')+1, date('y')+10),
            'cvc' => $this->faker->numberBetween(100, 999),
            'firstName' => $this->faker->firstName(),
            'lastName' => $this->faker->lastName,
            'billingAddress1' => $this->faker->streetAddress,
            'billingPostcode' => $this->faker->postcode,
        ];
    }
}
