<?php

namespace Tests\Shop\Traits;

use Faker\Factory;

trait GeneratesCreditCardPayments
{
    protected $faker;

    protected function generateCreditCardPaymentData()
    {
        if (!$this->faker) {
            $this->faker = Factory::create();
        }
        return [
            'number' => $this->faker->creditCardNumber(),
            'name' => $this->faker->firstName() . ' ' . $this->faker->lastName,
            'expiry' => $this->faker->numberBetween(1, 12) . '/' . $this->faker->numberBetween(date('y')+1, date('y')+10),
            'cvc' => $this->faker->numberBetween(100, 999),
            'billing-address' => $this->faker->streetAddress,
            'postal-code' => $this->faker->postcode,
        ];
    }
}
