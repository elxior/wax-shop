<?php

namespace Tests\Shop\Tax;

use Wax\Shop\Models\Order\ShippingRate;
use Wax\Shop\Models\Product;
use Wax\Shop\Models\Tax;
use Wax\Shop\Tax\Drivers\DbDriver;

class DbDriverTest extends TaxDriverTestCase
{
    public function setUp()
    {
        parent::setUp();

        config(['wax.shop.tax_driver' => DbDriver::class]);

        Tax::create([
            'zone' => 'KY',
            'rate' => 6,
            'tax_shipping' => true
        ]);
    }

    public function testInvalidAddress()
    {
        // this driver can't validate addresses
        return false;
    }

    public function testMultiShipment()
    {
        $zone = Tax::where('zone', 'KY')->firstOrFail();
        $zone->tax_shipping = true;
        $zone->save();

        parent::testMultiShipment();
    }

    public function testTaxShippingEnabled()
    {
        $zone = Tax::where('zone', 'KY')->firstOrFail();
        $zone->tax_shipping = true;
        $zone->save();

        $this->shopService->addOrderItem(
            factory(Product::class)->create(['price' => 10])->id
        );

        $this->setKyShippingAddress();

        $this->shopService->setShippingService(
            new ShippingRate(['amount' => '10'])
        );

        $order = $this->shopService->getActiveOrder();
        $order->default_shipment->calculateTax();

        $this->assertTrue($order->default_shipment->tax_shipping);
        $this->assertEquals('KY 6%', $order->default_shipment->tax_desc);
        $this->assertEquals(6, $order->default_shipment->tax_rate);
        $this->assertEquals(1.2, $order->default_shipment->tax_amount);
        $this->assertEquals(21.2, $order->default_shipment->gross_total);

        $order = $this->shopService->getActiveOrder();
        $this->assertEquals(1.2, $order->tax_subtotal);
        $this->assertEquals(21.2, $order->gross_total);
    }

    public function testTaxShippingDisabled()
    {
        $zone = Tax::where('zone', 'KY')->firstOrFail();
        $zone->tax_shipping = false;
        $zone->save();

        $this->shopService->addOrderItem(
            factory(Product::class)->create(['price' => 10])->id
        );

        $this->setKyShippingAddress();

        $this->shopService->setShippingService(
            new ShippingRate(['amount' => '10'])
        );

        $order = $this->shopService->getActiveOrder();
        $order->default_shipment->calculateTax();

        $this->assertFalse($order->default_shipment->tax_shipping);
        $this->assertEquals('KY 6%', $order->default_shipment->tax_desc);
        $this->assertEquals(6, $order->default_shipment->tax_rate);
        $this->assertEquals(.6, $order->default_shipment->tax_amount);
        $this->assertEquals(20.6, $order->default_shipment->gross_total);

        $order = $this->shopService->getActiveOrder();
        $this->assertEquals(.6, $order->tax_subtotal);
        $this->assertEquals(20.6, $order->gross_total);
    }
}
