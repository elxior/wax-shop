<?php

namespace Tests\Shop\Tax;

use Faker\Factory;
use Illuminate\Support\Collection;
use Tests\TestCase;
use Wax\Shop\Tax\Support\Address;
use Wax\Shop\Tax\Support\LineItem;
use Wax\Shop\Tax\Support\Request;
use Wax\Shop\Tax\Support\Shipping;

class RequestTest extends TestCase
{
    protected $faker;

    public function setUp()
    {
        $this->faker = Factory::create();
    }

    public function testSetters()
    {
        $requestId = $this->faker->numerify('Invoice-#####');
        $customerId = $this->faker->randomNumber(5);

        $address = new Address();
        $shipping = new Shipping();

        $request = (new Request)
            ->setRequestId($requestId)
            ->setCustomerId($customerId)
            ->setAddress($address)
            ->setShipping($shipping);

        for ($i = 0; $i < 3; $i++) {
            $request->addLineItem(new LineItem());
        }

        $this->assertEquals($requestId, $request->getRequestId());
        $this->assertEquals($customerId, $request->getCustomerId());
        $this->assertInstanceOf(Address::class, $request->getAddress());
        $this->assertInstanceOf(Shipping::class, $request->getShipping());

        $lineItems = $request->getLineItems();
        $this->assertInstanceOf(Collection::class, $lineItems);
        $this->assertEquals(3, $lineItems->count());
        $this->assertInstanceOf(LineItem::class, $lineItems->first());
    }
}
