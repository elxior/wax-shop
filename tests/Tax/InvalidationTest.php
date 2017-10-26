<?php

namespace Tests\Shop\Tax;

use Wax\Shop\Drivers\Tax\DbDriver;
use Wax\Shop\Facades\ShopServiceFacade;
use Wax\Shop\Models\Coupon;
use Wax\Shop\Models\Order\ShippingRate;
use Wax\Shop\Models\Product;
use Wax\Shop\Models\Tax;
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

        config(['wax.shop.tax_driver' => DbDriver::class]);

        Tax::create([
            'zone' => 'KY',
            'rate' => 6,
            'tax_shipping' => true
        ]);
    }

    public function testAddOrderItemInvalidatesTax()
    {
        ShopServiceFacade::addOrderItem(
            factory(Product::class)->create(['price' => 10])->id
        );
        $this->setKyShippingAddress();
        ShopServiceFacade::getActiveOrder()->calculateTax();

        $order = ShopServiceFacade::getActiveOrder();
        $this->assertEquals('KY 6%', $order->default_shipment->tax_desc);
        $this->assertEquals(6, $order->default_shipment->tax_rate);
        $this->assertEquals(.6, $order->default_shipment->tax_amount);
        $this->assertEquals(10.6, $order->default_shipment->total);

        ShopServiceFacade::addOrderItem(
            factory(Product::class)->create(['price' => 10])->id
        );

        $order = ShopServiceFacade::getActiveOrder();
        $this->assertNull($order->default_shipment->tax_desc);
        $this->assertNull($order->default_shipment->tax_rate);
        $this->assertNull($order->default_shipment->tax_amount);
        $this->assertEquals(20, $order->default_shipment->total);
    }

    public function testUpdateOrderItemQuantityInvalidatesTax()
    {
        ShopServiceFacade::addOrderItem(
            factory(Product::class)->create(['price' => 10])->id
        );
        $this->setKyShippingAddress();
        ShopServiceFacade::getActiveOrder()->calculateTax();

        $order = ShopServiceFacade::getActiveOrder();
        $this->assertEquals('KY 6%', $order->default_shipment->tax_desc);
        $this->assertEquals(6, $order->default_shipment->tax_rate);
        $this->assertEquals(.6, $order->default_shipment->tax_amount);
        $this->assertEquals(10.6, $order->default_shipment->total);

        ShopServiceFacade::updateOrderItemQuantity(1, 2);

        $order = ShopServiceFacade::getActiveOrder();
        $this->assertNull($order->default_shipment->tax_desc);
        $this->assertNull($order->default_shipment->tax_rate);
        $this->assertNull($order->default_shipment->tax_amount);
        $this->assertEquals(20, $order->default_shipment->total);
    }

    public function testDeleteOrderItemInvalidatesTax()
    {
        ShopServiceFacade::addOrderItem(
            factory(Product::class)->create(['price' => 10])->id
        );
        ShopServiceFacade::addOrderItem(
            factory(Product::class)->create(['price' => 10])->id
        );
        $this->setKyShippingAddress();
        ShopServiceFacade::getActiveOrder()->calculateTax();

        $order = ShopServiceFacade::getActiveOrder();
        $this->assertEquals('KY 6%', $order->default_shipment->tax_desc);
        $this->assertEquals(6, $order->default_shipment->tax_rate);
        $this->assertEquals(1.2, $order->default_shipment->tax_amount);
        $this->assertEquals(21.2, $order->default_shipment->total);

        ShopServiceFacade::deleteOrderItem(1);

        $order = ShopServiceFacade::getActiveOrder();
        $this->assertNull($order->default_shipment->tax_desc);
        $this->assertNull($order->default_shipment->tax_rate);
        $this->assertNull($order->default_shipment->tax_amount);
        $this->assertEquals(10, $order->default_shipment->total);
    }

    public function testSetShippingAddressInvalidatesTax()
    {
        ShopServiceFacade::addOrderItem(
            factory(Product::class)->create(['price' => 10])->id
        );
        $this->setKyShippingAddress();
        ShopServiceFacade::getActiveOrder()->calculateTax();

        $order = ShopServiceFacade::getActiveOrder();
        $this->assertEquals('KY 6%', $order->default_shipment->tax_desc);
        $this->assertEquals(6, $order->default_shipment->tax_rate);
        $this->assertEquals(.6, $order->default_shipment->tax_amount);
        $this->assertEquals(10.6, $order->default_shipment->total);

        $this->setKyShippingAddress();

        $order = ShopServiceFacade::getActiveOrder();
        $this->assertNull($order->default_shipment->tax_desc);
        $this->assertNull($order->default_shipment->tax_rate);
        $this->assertNull($order->default_shipment->tax_amount);
        $this->assertEquals(10, $order->default_shipment->total);
    }

    public function testSetShippingServiceInvalidatesTax()
    {
        ShopServiceFacade::addOrderItem(
            factory(Product::class)->create(['price' => 10])->id
        );
        $this->setKyShippingAddress();
        ShopServiceFacade::getActiveOrder()->calculateTax();

        $order = ShopServiceFacade::getActiveOrder();
        $this->assertEquals('KY 6%', $order->default_shipment->tax_desc);
        $this->assertEquals(6, $order->default_shipment->tax_rate);
        $this->assertEquals(.6, $order->default_shipment->tax_amount);
        $this->assertEquals(10.6, $order->default_shipment->total);

        ShopServiceFacade::setShippingService(factory(ShippingRate::class)->create(['amount' => 10]));

        $order = ShopServiceFacade::getActiveOrder();
        $this->assertNull($order->default_shipment->tax_desc);
        $this->assertNull($order->default_shipment->tax_rate);
        $this->assertNull($order->default_shipment->tax_amount);
        $this->assertEquals(20, $order->default_shipment->total);
    }

    public function testApplyCouponInvalidatesTax()
    {
        $coupon = factory(Coupon::class)
            ->create([
                'dollars' => 1,
                'minimum_order' => 10,
            ]);

        ShopServiceFacade::addOrderItem(
            factory(Product::class)->create(['price' => 10])->id
        );

        $this->setKyShippingAddress();
        ShopServiceFacade::getActiveOrder()->calculateTax();

        $order = ShopServiceFacade::getActiveOrder();
        $this->assertEquals('KY 6%', $order->default_shipment->tax_desc);
        $this->assertEquals(6, $order->default_shipment->tax_rate);
        $this->assertEquals(.6, $order->default_shipment->tax_amount);
        $this->assertEquals(10.6, $order->default_shipment->total);

        $this->assertTrue(ShopServiceFacade::applyCoupon($coupon->code));

        $order = ShopServiceFacade::getActiveOrder();
        $this->assertNull($order->default_shipment->tax_desc);
        $this->assertNull($order->default_shipment->tax_rate);
        $this->assertNull($order->default_shipment->tax_amount);
        $this->assertEquals(9, $order->default_shipment->total);
    }
}
