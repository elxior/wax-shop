<?php

namespace Tests\Shop\Tax;

use Faker\Factory;
use Tests\TestCase;
use Wax\Shop\Support\Tax\LineItem;

class LineItemTest extends TestCase
{
    protected $faker;

    public function setUp()
    {
        parent::setUp();

        $this->faker = Factory::create();
    }

    public function testConstructor()
    {
        $itemCode = $this->faker->uuid;
        $unitPrice = $this->faker->randomFloat(2, 1, 100);
        $quantity = $this->faker->randomDigit;
        $taxable = $this->faker->boolean;

        $item = new LineItem($itemCode, $unitPrice, $quantity, $taxable);

        $this->assertEquals($itemCode, $item->getItemCode());
        $this->assertEquals($unitPrice, $item->getUnitPrice());
        $this->assertEquals($quantity, $item->getQuantity());
        $this->assertEquals($taxable, $item->getTaxable());
    }

    public function testSetters()
    {
        $itemCode = $this->faker->uuid;
        $unitPrice = $this->faker->randomFloat(2, 1, 100);
        $quantity = $this->faker->randomDigit;
        $taxable = $this->faker->boolean;

        $item = (new LineItem)
            ->setItemCode($itemCode)
            ->setUnitPrice($unitPrice)
            ->setQuantity($quantity)
            ->setTaxable($taxable);

        $this->assertEquals($itemCode, $item->getItemCode());
        $this->assertEquals($unitPrice, $item->getUnitPrice());
        $this->assertEquals($quantity, $item->getQuantity());
        $this->assertEquals($taxable, $item->getTaxable());
    }
}
