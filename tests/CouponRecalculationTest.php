<?php

namespace Tests\Shop;

use Carbon\Carbon;
use Tests\Shop\Traits\SeedsProducts;
use Tests\Shop\ShopBaseTestCase;
use Wax\Shop\Models\Coupon;
use Wax\Shop\Models\Order\ShippingRate;
use Wax\Shop\Models\Product;
use Wax\Shop\Services\ShopService;

class CouponRecalculationTest extends ShopBaseTestCase
{
    use SeedsProducts;

    /* @var ShopService $shop */
    protected $shopService;

    public function setUp()
    {
        parent::setUp();

        $this->seedProducts();
        $this->shopService = app()->make(ShopService::class);
    }

    public function testAddOrderItemUpdatesCoupon()
    {
        $coupon = factory(Coupon::class)
            ->create([
                'dollars' => 1,
            ]);

        $this->assertTrue($this->shopService->applyCoupon($coupon->code));

        $order = $this->shopService->getActiveOrder();
        $this->assertEquals(0, $order->coupon->calculated_value);
        $this->assertEquals(0, $order->coupon_value);
        $this->assertEquals(0, $order->item_subtotal);
        $this->assertEquals(0, $order->total);

        $this->shopService->addOrderItem(factory(Product::class)->create(['price' => 10])->id);

        $order = $this->shopService->getActiveOrder();
        $this->assertEquals(1, $order->coupon->calculated_value);
        $this->assertEquals(1, $order->coupon_value);
        $this->assertNotEquals($order->item_gross_subtotal, $order->item_subtotal);
        $this->assertNotEquals($order->gross_total, $order->total);
        $this->assertEquals(1, $order->items->first()->discount_amount);
    }

    public function testUpdateOrderItemUpdatesCoupon()
    {
        $coupon = factory(Coupon::class)
            ->create([
                'percent' => 10,
            ]);

        $this->shopService->addOrderItem(factory(Product::class)->create(['price' => 10])->id);

        $this->assertTrue($this->shopService->applyCoupon($coupon->code));

        $order = $this->shopService->getActiveOrder();
        $this->assertEquals(1, $order->items->first()->discount_amount);
        $this->assertEquals(1, $order->coupon->calculated_value);
        $this->assertEquals(1, $order->coupon_value);
        $this->assertEquals(9, $order->item_subtotal);
        $this->assertEquals(9, $order->total);

        $this->shopService->updateOrderItemQuantity(1, 2);

        $order = $this->shopService->getActiveOrder();
        $this->assertEquals(2, $order->items->first()->discount_amount);
        $this->assertEquals(2, $order->coupon->calculated_value);
        $this->assertEquals(2, $order->coupon_value);
        $this->assertEquals(18, $order->item_subtotal);
        $this->assertEquals(18, $order->total);
    }

    public function testDeleteOrderItemUpdatesCoupon()
    {
        $coupon = factory(Coupon::class)
            ->create([
                'percent' => 10,
            ]);

        $this->shopService->addOrderItem(factory(Product::class)->create(['price' => 10])->id);

        $this->assertTrue($this->shopService->applyCoupon($coupon->code));

        $order = $this->shopService->getActiveOrder();
        $this->assertEquals(1, $order->items->first()->discount_amount);
        $this->assertEquals(1, $order->coupon->calculated_value);
        $this->assertEquals(1, $order->coupon_value);
        $this->assertEquals(9, $order->item_subtotal);
        $this->assertEquals(9, $order->total);

        $this->shopService->deleteOrderItem(1);

        $order = $this->shopService->getActiveOrder();
        $this->assertEquals(0, $order->coupon->calculated_value);
        $this->assertEquals(0, $order->coupon_value);
        $this->assertEquals(0, $order->item_subtotal);
        $this->assertEquals(0, $order->total);
    }

    public function testChangeCartInvalidatesMinimumOrderThreshold()
    {
        $coupon = factory(Coupon::class)
            ->create([
                'percent' => 10,
                'minimum_order' => 20
            ]);

        $this->shopService->addOrderItem(factory(Product::class)->create(['price' => 10])->id, 2);

        $this->assertTrue($this->shopService->applyCoupon($coupon->code));

        $order = $this->shopService->getActiveOrder();
        $this->assertEquals(2, $order->items->first()->discount_amount);
        $this->assertEquals(2, $order->coupon->calculated_value);
        $this->assertEquals(2, $order->coupon_value);
        $this->assertEquals(18, $order->item_subtotal);
        $this->assertEquals(18, $order->total);

        $this->shopService->updateOrderItemQuantity(1, 1);

        $order = $this->shopService->getActiveOrder();
        $this->assertNull($order->coupon);
        $this->assertEquals(0, $order->coupon_value);
        $this->assertEquals(10, $order->item_subtotal);
        $this->assertEquals(10, $order->total);
    }

    public function testChangeShippingUpdatesCoupon()
    {
        $coupon = factory(Coupon::class)
            ->create([
                'percent' => 10,
                'include_shipping' => true
            ]);

        $this->assertTrue($this->shopService->applyCoupon($coupon->code));

        $order = $this->shopService->getActiveOrder();
        $this->assertEquals(0, $order->coupon->calculated_value);
        $this->assertEquals(0, $order->coupon_value);
        $this->assertEquals(0, $order->item_subtotal);
        $this->assertEquals(0, $order->total);

        $this->shopService->setShippingService(factory(ShippingRate::class)->create(['amount' => 10]));

        $order = $this->shopService->getActiveOrder();
        $this->assertEquals(1, $order->coupon->calculated_value);
        $this->assertEquals(1, $order->coupon_value);
        $this->assertEquals(0, $order->item_subtotal);
        $this->assertEquals(10, $order->shipping_gross_subtotal);
        $this->assertEquals(1, $order->shipping_discount_amount);
        $this->assertEquals(9, $order->shipping_subtotal);
        $this->assertEquals(9, $order->total);
    }
}
