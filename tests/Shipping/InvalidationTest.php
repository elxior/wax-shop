<?php

namespace Tests\Shop\Shipping;

use Wax\Shop\Facades\ShopServiceFacade;

use Wax\Shop\Models\Order\ShippingRate;
use Wax\Shop\Models\Product;
use Faker\Factory;
use Tests\Shop\Traits\SetsShippingAddress;
use Tests\WaxAppTestCase;

class InvalidationTest extends WaxAppTestCase
{
    use SetsShippingAddress;

    /* @var \Faker\Generator */
    protected $faker;

    public function setUp()
    {
        parent::setUp();
        $this->faker = Factory::create();
    }

    public function testAddOrderItemInvalidatesShipping()
    {
        ShopServiceFacade::addOrderItem(
            factory(Product::class)->create(['price' => 10])->id
        );
        $rates = factory(ShippingRate::class, 4)->create();

        ShopServiceFacade::getActiveOrder()->default_shipment->rates()->saveMany($rates);
        $this->assertEquals($rates->count(), ShopServiceFacade::getActiveOrder()->default_shipment->rates->count());

        $selectedRate = $rates->random();
        $this->assertTrue(ShopServiceFacade::setShippingService($selectedRate));

        $order = ShopServiceFacade::getActiveOrder();

        $this->assertEquals($selectedRate->carrier, $order->default_shipment->shipping_carrier);
        $this->assertEquals($selectedRate->service_code, $order->default_shipment->shipping_service_code);
        $this->assertEquals($selectedRate->service_name, $order->default_shipment->shipping_service_name);
        $this->assertEquals($selectedRate->business_transit_days, $order->default_shipment->business_transit_days);

        $this->assertEquals($selectedRate->amount, $order->default_shipment->shipping_service_amount);
        $this->assertEquals($selectedRate->amount, $order->default_shipment->shipping_gross_subtotal);
        $this->assertEquals($selectedRate->amount, $order->shipping_gross_subtotal);

        ShopServiceFacade::addOrderItem(
            factory(Product::class)->create(['price' => 10])->id
        );

        $order = ShopServiceFacade::getActiveOrder();
        $this->assertNull($order->default_shipment->shipping_carrier);
        $this->assertNull($order->default_shipment->shipping_service_code);
        $this->assertNull($order->default_shipment->shipping_service_name);
        $this->assertNull($order->default_shipment->business_transit_days);

        $this->assertNull($order->default_shipment->shipping_service_amount);
        $this->assertNull($order->default_shipment->shipping_discount_amount);
        $this->assertEquals(0, $order->default_shipment->shipping_gross_subtotal);
        $this->assertEquals(0, $order->shipping_gross_subtotal);

        $this->assertEquals(0, $order->default_shipment->rates->count());
    }

    public function testUpdateOrderItemQuantityInvalidatesShipping()
    {
        ShopServiceFacade::addOrderItem(
            factory(Product::class)->create(['price' => 10])->id
        );
        $rates = factory(ShippingRate::class, 4)->create();

        ShopServiceFacade::getActiveOrder()->default_shipment->rates()->saveMany($rates);
        $this->assertEquals($rates->count(), ShopServiceFacade::getActiveOrder()->default_shipment->rates->count());

        $selectedRate = $rates->random();
        $this->assertTrue(ShopServiceFacade::setShippingService($selectedRate));

        $order = ShopServiceFacade::getActiveOrder();
        $this->assertEquals($selectedRate->carrier, $order->default_shipment->shipping_carrier);
        $this->assertEquals($selectedRate->service_code, $order->default_shipment->shipping_service_code);
        $this->assertEquals($selectedRate->service_name, $order->default_shipment->shipping_service_name);
        $this->assertEquals($selectedRate->business_transit_days, $order->default_shipment->business_transit_days);

        $this->assertEquals($selectedRate->amount, $order->default_shipment->shipping_service_amount);
        $this->assertEquals($selectedRate->amount, $order->default_shipment->shipping_gross_subtotal);
        $this->assertEquals($selectedRate->amount, $order->shipping_gross_subtotal);

        ShopServiceFacade::updateOrderItemQuantity(1, 2);

        $order->refresh();
        $this->assertNull($order->default_shipment->shipping_carrier);
        $this->assertNull($order->default_shipment->shipping_service_code);
        $this->assertNull($order->default_shipment->shipping_service_name);
        $this->assertNull($order->default_shipment->business_transit_days);

        $this->assertNull($order->default_shipment->shipping_service_amount);
        $this->assertNull($order->default_shipment->shipping_discount_amount);
        $this->assertEquals(0, $order->default_shipment->shipping_gross_subtotal);
        $this->assertEquals(0, $order->shipping_gross_subtotal);

        $this->assertEquals(0, $order->default_shipment->rates->count());
    }

    public function testDeleteOrderItemInvalidatesShipping()
    {
        ShopServiceFacade::addOrderItem(
            factory(Product::class)->create(['price' => 10])->id
        );
        $rates = factory(ShippingRate::class, 4)->create();

        ShopServiceFacade::getActiveOrder()->default_shipment->rates()->saveMany($rates);
        $this->assertEquals($rates->count(), ShopServiceFacade::getActiveOrder()->default_shipment->rates->count());

        $selectedRate = $rates->random();
        $this->assertTrue(ShopServiceFacade::setShippingService($selectedRate));

        $order = ShopServiceFacade::getActiveOrder();
        $this->assertEquals($selectedRate->carrier, $order->default_shipment->shipping_carrier);
        $this->assertEquals($selectedRate->service_code, $order->default_shipment->shipping_service_code);
        $this->assertEquals($selectedRate->service_name, $order->default_shipment->shipping_service_name);
        $this->assertEquals($selectedRate->business_transit_days, $order->default_shipment->business_transit_days);

        $this->assertEquals($selectedRate->amount, $order->default_shipment->shipping_service_amount);
        $this->assertEquals($selectedRate->amount, $order->default_shipment->shipping_gross_subtotal);
        $this->assertEquals($selectedRate->amount, $order->shipping_gross_subtotal);

        ShopServiceFacade::deleteOrderItem(1);

        $order->refresh();
        $this->assertNull($order->default_shipment->shipping_carrier);
        $this->assertNull($order->default_shipment->shipping_service_code);
        $this->assertNull($order->default_shipment->shipping_service_name);
        $this->assertNull($order->default_shipment->business_transit_days);

        $this->assertNull($order->default_shipment->shipping_service_amount);
        $this->assertNull($order->default_shipment->shipping_discount_amount);
        $this->assertEquals(0, $order->default_shipment->shipping_gross_subtotal);
        $this->assertEquals(0, $order->shipping_gross_subtotal);

        $this->assertEquals(0, $order->default_shipment->rates->count());
    }

    public function testSetShippingAddressInvalidatesShipping()
    {
        ShopServiceFacade::addOrderItem(
            factory(Product::class)->create(['price' => 10])->id
        );

        $rates = factory(ShippingRate::class, 4)->create();

        ShopServiceFacade::getActiveOrder()->default_shipment->rates()->saveMany($rates);
        $this->assertEquals($rates->count(), ShopServiceFacade::getActiveOrder()->default_shipment->rates->count());

        $selectedRate = $rates->random();
        ShopServiceFacade::setShippingService($selectedRate);

        $order = ShopServiceFacade::getActiveOrder();
        $this->assertEquals($selectedRate->carrier, $order->default_shipment->shipping_carrier);
        $this->assertEquals($selectedRate->service_code, $order->default_shipment->shipping_service_code);
        $this->assertEquals($selectedRate->service_name, $order->default_shipment->shipping_service_name);
        $this->assertEquals($selectedRate->business_transit_days, $order->default_shipment->business_transit_days);

        $this->assertEquals($selectedRate->amount, $order->default_shipment->shipping_service_amount);
        $this->assertEquals($selectedRate->amount, $order->default_shipment->shipping_gross_subtotal);
        $this->assertEquals($selectedRate->amount, $order->shipping_gross_subtotal);

        $this->setKyShippingAddress();

        $order->refresh();
        $this->assertNull($order->default_shipment->shipping_carrier);
        $this->assertNull($order->default_shipment->shipping_service_code);
        $this->assertNull($order->default_shipment->shipping_service_name);
        $this->assertNull($order->default_shipment->business_transit_days);

        $this->assertNull($order->default_shipment->shipping_service_amount);
        $this->assertNull($order->default_shipment->shipping_discount_amount);
        $this->assertEquals(0, $order->default_shipment->shipping_gross_subtotal);
        $this->assertEquals(0, $order->shipping_gross_subtotal);

        $this->assertEquals(0, $order->default_shipment->rates->count());
    }
}
