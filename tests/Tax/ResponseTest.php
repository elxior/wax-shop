<?php

namespace Tests\Shop\Tax;

use App\Shop\Support\Tax\Response;
use Faker\Factory;
use Tests\TestCase;

class ResponseTest extends TestCase
{
    protected $faker;

    public function setUp()
    {
        parent::setUp();

        $this->faker = Factory::create();
    }

    public function testSetters()
    {
        $description = $this->faker->bs;
        $rate = $this->faker->randomFloat(2, .2, .6) / 4;
        $amount = $this->faker->randomFloat(2, 1, 100);
        $taxShipping = $this->faker->boolean;

        $response = (new Response)
            ->setDescription($description)
            ->setRate($rate)
            ->setAmount($amount)
            ->setTaxShipping($taxShipping);

        $this->assertEquals($description, $response->getDescription());
        $this->assertEquals($rate, $response->getRate());
        $this->assertEquals($amount, $response->getAmount());
        $this->assertEquals($taxShipping, $response->getTaxShipping());
    }
}
