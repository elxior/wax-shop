<?php

namespace Tests\Shop\Tax;

use Faker\Factory;
use Tests\Shop\Traits\SetsShippingAddress;
use Tests\Shop\Support\ShopBaseTestCase;
use Wax\Shop\Models\Order\ShippingRate;
use Wax\Shop\Models\Product;
use Wax\Shop\Services\ShopService;
use Wax\Shop\Tax\Exceptions\AddressException;

class TaxDriverTestCase extends ShopBaseTestCase
{
    use SetsShippingAddress;

    /* @var \Wax\Shop\Services\ShopService */
    protected $shopService;

    /* @var \Faker\Generator */
    protected $faker;

    public function setUp()
    {
        parent::setUp();

        $this->shopService = app()->make(ShopService::class);
        $this->faker = Factory::create();
    }

    public function testInvalidAddress()
    {
        $this->shopService->addOrderItem(
            factory(Product::class)->create(['price' => 10])->id
        );

        $this->shopService->setShippingAddress(
            $this->faker->firstName,
            $this->faker->lastName,
            $this->faker->company,
            $this->faker->safeEmail,
            $this->faker->phoneNumber,
            '101 Bork Street',
            $this->faker->secondaryAddress,
            'Borksburg',
            'BK',
            '87653-0909',
            'US'
        );

        $order = $this->shopService->getActiveOrder();

        $this->expectException(AddressException::class);
        $order->default_shipment->calculateTax();
    }

    public function testGetTax()
    {
        $this->shopService->addOrderItem(
            factory(Product::class)->create(['price' => 10])->id
        );

        $this->setKyShippingAddress();

        $order = $this->shopService->getActiveOrder();
        $order->default_shipment->calculateTax();

        $this->assertEquals('KY 6%', $order->default_shipment->tax_desc);
        $this->assertEquals(6, $order->default_shipment->tax_rate);
        $this->assertEquals(.6, $order->default_shipment->tax_amount);
        $this->assertEquals(10.6, $order->default_shipment->gross_total);

        $order = $this->shopService->getActiveOrder();
        $this->assertEquals(.6, $order->tax_subtotal);
        $this->assertEquals(10.6, $order->gross_total);
    }

    public function testCommit()
    {
        $this->shopService->addOrderItem(
            factory(Product::class)->create(['price' => 10])->id
        );

        $this->setKyShippingAddress();

        $shipment = $this->shopService->getActiveOrder()->default_shipment;

        $result = $shipment->commitTax();

        $this->assertTrue($result);
    }

    public function testMultiItem()
    {
        $this->shopService->addOrderItem(
            factory(Product::class)->create(['price' => 10])->id
        );

        $this->shopService->addOrderItem(
            factory(Product::class)->create(['price' => 20])->id
        );

        $this->shopService->addOrderItem(
            factory(Product::class)->create(['price' => 10])->id,
            2
        );

        $this->setKyShippingAddress();

        $order = $this->shopService->getActiveOrder();
        $order->default_shipment->calculateTax();

        $this->assertEquals('KY 6%', $order->default_shipment->tax_desc);
        $this->assertEquals(6, $order->default_shipment->tax_rate);
        $this->assertEquals(3, $order->default_shipment->tax_amount);
        $this->assertEquals(53, $order->default_shipment->gross_total);

        $order = $this->shopService->getActiveOrder();
        $this->assertEquals(3, $order->tax_subtotal);
        $this->assertEquals(53, $order->gross_total);
    }

    public function testMultiShipment()
    {
        $order = $this->shopService->getActiveOrder();
        $order->shipments()->create([]);
        $order->shipments()->create([]);
        $order->refresh();

        $order->shipments[0]->addItem(
            factory(Product::class)->create(['price' => 10])->id
        );

        $order->shipments[1]->addItem(
            factory(Product::class)->create(['price' => 20])->id,
            2
        );

        $order->shipments[0]->setAddress(
            $this->faker->firstName,
            $this->faker->lastName,
            $this->faker->company,
            $this->faker->safeEmail,
            $this->faker->phoneNumber,
            $this->faker->streetAddress,
            $this->faker->secondaryAddress,
            'Louisville',
            'KY',
            '40203',
            'US'
        );

        $order->shipments[1]->setAddress(
            $this->faker->firstName,
            $this->faker->lastName,
            $this->faker->company,
            $this->faker->safeEmail,
            $this->faker->phoneNumber,
            $this->faker->streetAddress,
            $this->faker->secondaryAddress,
            'Louisville',
            'KY',
            '40203',
            'US'
        );

        $order->shipments[0]->setShippingService(factory(ShippingRate::class)->create(['amount' => 5]));

        $order->shipments[1]->setShippingService(factory(ShippingRate::class)->create(['amount' => 10]));

        $this->shopService->calculateTax();
        $order = $this->shopService->getActiveOrder();

        $this->assertEquals('KY 6%', $order->shipments[0]->tax_desc);
        $this->assertEquals(6, $order->shipments[0]->tax_rate);
        $this->assertEquals(.9, $order->shipments[0]->tax_amount);
        $this->assertTrue($order->shipments[0]->tax_shipping);
        $this->assertEquals(15.9, $order->shipments[0]->gross_total);

        $this->assertEquals('KY 6%', $order->shipments[1]->tax_desc);
        $this->assertEquals(6, $order->shipments[1]->tax_rate);
        $this->assertEquals(3, $order->shipments[1]->tax_amount);
        $this->assertTrue($order->shipments[1]->tax_shipping);
        $this->assertEquals(53, $order->shipments[1]->gross_total);

        $order = $this->shopService->getActiveOrder();
        $this->assertEquals(3.9, $order->tax_subtotal);
        $this->assertEquals(68.9, $order->gross_total);
    }

    public function testMixedTaxability()
    {
        $this->shopService->addOrderItem(
            factory(Product::class)->create(['price' => 10, 'taxable' => true])->id
        );

        $this->shopService->addOrderItem(
            factory(Product::class)->create(['price' => 10, 'taxable' => false])->id
        );

        $this->setKyShippingAddress();

        $order = $this->shopService->getActiveOrder();
        $order->default_shipment->calculateTax();

        $this->assertEquals('KY 6%', $order->default_shipment->tax_desc);
        $this->assertEquals(6, $order->default_shipment->tax_rate);
        $this->assertEquals(.6, $order->default_shipment->tax_amount);
        $this->assertEquals(20.6, $order->default_shipment->gross_total);

        $order = $this->shopService->getActiveOrder();
        $this->assertEquals(.6, $order->tax_subtotal);
        $this->assertEquals(20.6, $order->gross_total);
    }
}
