<?php

namespace Tests\Shop\Shipping;

use App\Shop\Models\Order\ShippingRate;
use App\Shop\Models\Product;
use App\Shop\Services\ShopService;
use Tests\WaxAppTestCase;

class ShippingTest extends WaxAppTestCase
{
    /* @var ShopService $shop */
    protected $shopService;

    public function setUp()
    {
        parent::setUp();
        $this->shopService = app()->make(ShopService::class);
    }

    public function testRequireCarrier()
    {
        $product = factory(Product::class)->create(['shipping_enable_rate_lookup' => true]);
        $product2 = factory(Product::class)->create(['shipping_enable_rate_lookup' => false]);

        $order = $this->shopService->getActiveOrder();

        $order->shipments()->create([]);
        $order->shipments()->create([]);

        $order = $this->shopService->getActiveOrder();

        $order->shipments[0]->addItem($product->id);
        $order->shipments[1]->addItem($product2->id);

        $order = $this->shopService->getActiveOrder();

        $this->assertTrue($order->shipments[0]->require_carrier);
        $this->assertFalse($order->shipments[1]->require_carrier);
    }

    public function testFlatShippingCalculation()
    {
        $product = factory(Product::class)->create(['shipping_flat_rate' => 2.99]);
        $product2 = factory(Product::class)->create(['shipping_flat_rate' => 5.99]);

        $order = $this->shopService->getActiveOrder();

        $order->shipments()->create([]);
        $order->shipments()->create([]);
        $order->refresh();

        $order->shipments[0]->addItem($product->id);
        $order->shipments[0]->addItem($product2->id);
        $order->shipments[1]->addItem($product->id, 2);

        $order->refresh();

        // test individual items
        $this->assertEquals(2.99, $order->shipments[0]->items->first()->flat_shipping_subtotal);
        $this->assertEquals(5.99, $order->shipments[0]->items->last()->flat_shipping_subtotal);
        $this->assertEquals(5.98, $order->shipments[1]->items->first()->flat_shipping_subtotal);

        // test shipments
        $this->assertEquals(8.98, $order->shipments[0]->flat_shipping_subtotal);
        $this->assertEquals(5.98, $order->shipments[1]->flat_shipping_subtotal);

        // test order total
        $this->assertEquals(14.96, $order->flat_shipping_subtotal);
    }

    public function testShipmentSetService()
    {
        $this->shopService->addOrderItem(factory(Product::class)->create()->id);

        $rates = [
            factory(ShippingRate::class)->create(),
            factory(ShippingRate::class)->create(),
        ];

        $order = $this->shopService->getActiveOrder();
        $order->default_shipment
            ->rates()
            ->saveMany($rates);

        $this->assertTrue($this->shopService->setShippingService($rates[0]));

        $this->assertGreaterThan(0, $rates[0]->amount);

        $order->refresh();
        $this->assertEquals($rates[0]->carrier, $order->default_shipment->shipping_carrier);
        $this->assertEquals($rates[0]->service_code, $order->default_shipment->shipping_service_code);
        $this->assertEquals($rates[0]->service_name, $order->default_shipment->shipping_service_name);
        $this->assertEquals($rates[0]->business_transit_days, $order->default_shipment->business_transit_days);

        $this->assertEquals($rates[0]->amount, $order->default_shipment->shipping_service_amount);
        $this->assertEquals($rates[0]->amount, $order->default_shipment->shipping_gross_subtotal);
        $this->assertEquals($rates[0]->amount, $order->shipping_gross_subtotal);
    }

    public function testMultiShipmentCombinedRates()
    {
        $order = $this->shopService->getActiveOrder();

        $order->shipments()->create([]);
        $order->shipments()->create([]);
        $order->refresh();

        $order->shipments[0]->addItem(factory(Product::class)->create(['shipping_flat_rate' => 2.99])->id);
        $order->shipments[1]->addItem(factory(Product::class)->create(['shipping_flat_rate' => 5.99])->id, 2);

        $rates = [
            factory(ShippingRate::class)->create(),
            factory(ShippingRate::class)->create(),
        ];

        $order->shipments[0]->setShippingService($rates[0]);
        $order->shipments[1]->setShippingService($rates[1]);

        $order->refresh();
        $this->assertEquals(2.99 + $rates[0]->amount, $order->shipments[0]->shipping_gross_subtotal);
        $this->assertEquals(11.98 + $rates[1]->amount, $order->shipments[1]->shipping_gross_subtotal);

        $this->assertEquals(14.97, $order->flat_shipping_subtotal);
        $this->assertEquals(($rates[0]->amount + $rates[1]->amount), $order->shipping_service_subtotal);

        $this->assertEquals((14.97 + $rates[0]->amount + $rates[1]->amount), $order->shipping_gross_subtotal);
    }
}
