<?php

namespace Tests\Shop\Tax;

use App\Shop\Support\Tax\Shipping;
use Faker\Factory;
use Tests\TestCase;

class ShippingTest extends TestCase
{
    protected $faker;

    public function setUp()
    {
        parent::setUp();

        $this->faker = Factory::create();
    }

    public function testConstructor()
    {
        $description = $this->faker->bs;
        $amount = $this->faker->randomFloat(2, 1, 100);

        $shipping = new Shipping($description, $amount);

        $this->assertEquals($description, $shipping->getDescription());
        $this->assertEquals($amount, $shipping->getAmount());
    }

    public function testSetters()
    {
        $description = $this->faker->bs;
        $amount = $this->faker->randomFloat(2, 1, 100);

        $shipping = (new Shipping)
            ->setDescription($description)
            ->setAmount($amount);

        $this->assertEquals($description, $shipping->getDescription());
        $this->assertEquals($amount, $shipping->getAmount());
    }
}
