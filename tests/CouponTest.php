<?php

namespace Tests\Shop;

use Carbon\Carbon;
use Tests\Shop\Traits\SeedsProducts;
use Tests\Shop\Support\ShopBaseTestCase;
use Wax\Shop\Models\Coupon;
use Wax\Shop\Models\Order\ShippingRate;
use Wax\Shop\Models\Product;
use Wax\Shop\Services\ShopService;

class CouponTest extends ShopBaseTestCase
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

    public function testMinimumOrder()
    {
        $coupon = factory(Coupon::class)
            ->create([
                'dollars' => 20,
                'minimum_order' => 50,
            ]);

        $this->products['basic']->price = 20;
        $this->products['basic']->save();

        $this->products['notDiscountable']->price = 20;
        $this->products['notDiscountable']->save();

        // minimum order has not been met
        $this->assertFalse($this->shopService->applyCoupon($coupon->code));

        // minimum order has still not been met
        $this->shopService->addOrderItem($this->products['notDiscountable']->id, 3);
        $this->assertFalse($this->shopService->applyCoupon($coupon->code));

        // almost...
        $this->shopService->addOrderItem($this->products['basic']->id, 2);
        $this->assertFalse($this->shopService->applyCoupon($coupon->code));

        // good to go
        $this->shopService->addOrderItem($this->products['basic']->id);
        $this->assertTrue($this->shopService->applyCoupon($coupon->code));
    }

    public function testApplyExpiredCoupon()
    {
        $coupon = factory(Coupon::class)
            ->create([
                'percent' => 10,
                'expired_at' => Carbon::yesterday(),
            ]);

        $product = factory(Product::class)->create(['price' => 10]);
        $this->shopService->addOrderItem($product->id);

        $this->assertFalse($this->shopService->applyCoupon($coupon->code));

        $order = $this->shopService->getActiveOrder();

        $this->assertEquals($order->item_gross_subtotal, $order->item_subtotal);
        $this->assertEquals($order->shipping_gross_subtotal, $order->shipping_subtotal);
        $this->assertEquals($order->gross_total, $order->total);
    }

    public function testAppliedCouponExpires()
    {
        $coupon = factory(Coupon::class)
            ->create([
                'percent' => 10,
                'include_shipping' => true,
            ]);

        $product = factory(Product::class)->create(['price' => 10, 'shipping_flat_rate' => 1.99]);
        $this->shopService->addOrderItem($product->id);

        $this->assertTrue($this->shopService->applyCoupon($coupon->code));

        $order = $this->shopService->getActiveOrder();

        $this->assertNotEquals($order->item_gross_subtotal, $order->item_subtotal);
        $this->assertNotEquals($order->shipping_gross_subtotal, $order->shipping_subtotal);
        $this->assertNotEquals($order->gross_total, $order->total);

        $order->coupon->expired_at = Carbon::yesterday();
        $order->coupon->save();

        $order = $this->shopService->getActiveOrder();

        $this->assertEquals($order->item_gross_subtotal, $order->item_subtotal);
        $this->assertEquals($order->shipping_gross_subtotal, $order->shipping_subtotal);
        $this->assertEquals($order->gross_total, $order->total);
    }

    public function testFixedAmountCoupon()
    {
        $coupon = factory(Coupon::class)
            ->create([
                'dollars' => 20,
                'minimum_order' => 50,
            ]);

        $this->products['basic']->price = 20;
        $this->products['basic']->save();

        $this->products['notDiscountable']->price = 20;
        $this->products['notDiscountable']->save();

        $this->shopService->addOrderItem($this->products['notDiscountable']->id, 2);
        $this->shopService->addOrderItem($this->products['basic']->id, 3);

        $this->assertTrue($this->shopService->applyCoupon($coupon->code));

        $order = $this->shopService->getActiveOrder();
        $this->assertEquals($coupon->dollars, $order->coupon->calculated_value);
        $this->assertEquals($coupon->dollars, $order->coupon_value);
    }

    public function testFixedAmountCouponLessThanOrderTotal()
    {
        $coupon = factory(Coupon::class)
            ->create([
                'dollars' => 20,
            ]);

        $this->products['basic']->price = 10;
        $this->products['basic']->save();

        $this->shopService->addOrderItem($this->products['basic']->id, 1);

        $this->assertTrue($this->shopService->applyCoupon($coupon->code));

        $order = $this->shopService->getActiveOrder();
        $this->assertEquals(20, $order->coupon->dollars);
        $this->assertEquals(10, $order->coupon->calculated_value);
        $this->assertEquals(10, $order->coupon_value);

        $this->assertEquals(10, $order->items->first()->discount_amount);

        $this->assertEquals(10, $order->gross_total);
        $this->assertEquals(0, $order->total);
    }

    public function testPercentBasedCoupon()
    {
        $coupon = factory(Coupon::class)
            ->create([
                'percent' => 20,
                'minimum_order' => 50,
            ]);

        $this->products['basic']->price = 20;
        $this->products['basic']->save();

        $this->shopService->addOrderItem($this->products['notDiscountable']->id, 2);
        $this->shopService->addOrderItem($this->products['basic']->id, 3);

        $this->assertTrue($this->shopService->applyCoupon($coupon->code));

        $order = $this->shopService->getActiveOrder();
        $this->assertEquals(60, $order->items->last()->gross_subtotal);
        $this->assertEquals(48, $order->items->last()->subtotal);
        $this->assertEquals(12, $order->coupon->calculated_value);
        $this->assertEquals(12, $order->coupon_value);
    }

    public function testCouponCartDistribution()
    {
        $coupon = factory(Coupon::class)
            ->create([
                'dollars' => 10,
                'minimum_order' => 30,
            ]);

        $this->shopService->addOrderItem(factory(Product::class)->create(['price' => 10])->id);
        $this->shopService->addOrderItem(factory(Product::class)->create(['price' => 10])->id);
        $this->shopService->addOrderItem(factory(Product::class)->create(['price' => 10])->id);

        $this->assertTrue($this->shopService->applyCoupon($coupon->code));

        $order = $this->shopService->getActiveOrder();

        $this->assertEquals([10, 10, 10], $order->items->pluck('gross_subtotal')->toArray());
        $this->assertEquals([3.34, 3.33, 3.33], $order->items->pluck('discount_amount')->toArray());
        $this->assertEquals([6.66, 6.67, 6.67], $order->items->pluck('subtotal')->toArray());
    }

    public function testIncludeShippingWithPercentage()
    {
        $coupon = factory(Coupon::class)
            ->create([
                'percent' => 20,
                'include_shipping' => true
            ]);

        $this->shopService->addOrderItem(factory(Product::class)->create(['price' => 100])->id);
        $this->shopService->setShippingService(factory(ShippingRate::class)->create(['amount' => 10]));

        $this->assertTrue($this->shopService->applyCoupon($coupon->code));

        $order = $this->shopService->getActiveOrder();

        // cart item subtotals
        $this->assertEquals(100, $order->default_shipment->item_gross_subtotal);
        $this->assertEquals(20, $order->default_shipment->item_discount_amount);
        $this->assertEquals(80, $order->default_shipment->item_subtotal);

        // shipping subtotals
        $this->assertEquals(10, $order->default_shipment->shipping_gross_subtotal);
        $this->assertEquals(2, $order->default_shipment->shipping_discount_amount);
        $this->assertEquals(8, $order->default_shipment->shipping_subtotal);

        // shipment totals
        $this->assertEquals(110, $order->default_shipment->gross_total);
        $this->assertEquals(88, $order->default_shipment->total);

        // order totals
        $this->assertEquals(100, $order->item_gross_subtotal);
        $this->assertEquals(20, $order->item_discount_amount);
        $this->assertEquals(80, $order->item_subtotal);

        $this->assertEquals(10, $order->shipping_gross_subtotal);
        $this->assertEquals(2, $order->shipping_discount_amount);
        $this->assertEquals(8, $order->shipping_subtotal);

        $this->assertEquals(110, $order->gross_total);
        $this->assertEquals(22, $order->coupon_value);
        $this->assertEquals(88, $order->total);

    }

    public function testIncludeShippingWithNotEnoughToCoverAnyShipping()
    {
        $coupon = factory(Coupon::class)
            ->create([
                'dollars' => 100,
                'include_shipping' => true
            ]);

        $this->shopService->addOrderItem(factory(Product::class)->create(['price' => 100])->id);
        $this->shopService->setShippingService(factory(ShippingRate::class)->create(['amount' => 10]));

        $this->assertTrue($this->shopService->applyCoupon($coupon->code));

        $order = $this->shopService->getActiveOrder();

        // cart item subtotals
        $this->assertEquals(100, $order->default_shipment->item_gross_subtotal);
        $this->assertEquals(100, $order->default_shipment->item_discount_amount);
        $this->assertEquals(0, $order->default_shipment->item_subtotal);

        // shipping subtotals
        $this->assertEquals(10, $order->default_shipment->shipping_gross_subtotal);
        $this->assertEquals(0, $order->default_shipment->shipping_discount_amount);
        $this->assertEquals(10, $order->default_shipment->shipping_subtotal);

        // shipment totals
        $this->assertEquals(110, $order->default_shipment->gross_total);
        $this->assertEquals(10, $order->default_shipment->total);

        // order totals
        $this->assertEquals(100, $order->item_gross_subtotal);
        $this->assertEquals(100, $order->item_discount_amount);
        $this->assertEquals(0, $order->item_subtotal);

        $this->assertEquals(10, $order->shipping_gross_subtotal);
        $this->assertEquals(0, $order->shipping_discount_amount);
        $this->assertEquals(10, $order->shipping_subtotal);

        $this->assertEquals(110, $order->gross_total);
        $this->assertEquals(100, $order->coupon_value);
        $this->assertEquals(10, $order->total);

    }

    public function testIncludeShippingWithDollarEnoughToCoverSomeShipping()
    {
        $coupon = factory(Coupon::class)
            ->create([
                'dollars' => 105,
                'include_shipping' => true
            ]);

        $this->shopService->addOrderItem(factory(Product::class)->create(['price' => 100])->id);
        $this->shopService->setShippingService(factory(ShippingRate::class)->create(['amount' => 10]));

        $this->assertTrue($this->shopService->applyCoupon($coupon->code));

        $order = $this->shopService->getActiveOrder();

        // cart item subtotals
        $this->assertEquals(100, $order->default_shipment->item_gross_subtotal);
        $this->assertEquals(100, $order->default_shipment->item_discount_amount);
        $this->assertEquals(0, $order->default_shipment->item_subtotal);

        // shipping subtotals
        $this->assertEquals(10, $order->default_shipment->shipping_gross_subtotal);
        $this->assertEquals(5, $order->default_shipment->shipping_discount_amount);
        $this->assertEquals(5, $order->default_shipment->shipping_subtotal);

        // shipment totals
        $this->assertEquals(110, $order->default_shipment->gross_total);
        $this->assertEquals(5, $order->default_shipment->total);

        // order totals
        $this->assertEquals(100, $order->item_gross_subtotal);
        $this->assertEquals(100, $order->item_discount_amount);
        $this->assertEquals(0, $order->item_subtotal);

        $this->assertEquals(10, $order->shipping_gross_subtotal);
        $this->assertEquals(5, $order->shipping_discount_amount);
        $this->assertEquals(5, $order->shipping_subtotal);

        $this->assertEquals(110, $order->gross_total);
        $this->assertEquals(105, $order->coupon_value);
        $this->assertEquals(5, $order->total);

    }
    public function testIncludeShippingWithDollarEnoughToCoverAllShipping()
    {
        $coupon = factory(Coupon::class)
            ->create([
                'dollars' => 110,
                'include_shipping' => true
            ]);

        $this->shopService->addOrderItem(factory(Product::class)->create(['price' => 100])->id);
        $this->shopService->setShippingService(factory(ShippingRate::class)->create(['amount' => 10]));

        $this->assertTrue($this->shopService->applyCoupon($coupon->code));

        $order = $this->shopService->getActiveOrder();

        // cart item subtotals
        $this->assertEquals(100, $order->default_shipment->item_gross_subtotal);
        $this->assertEquals(100, $order->default_shipment->item_discount_amount);
        $this->assertEquals(0, $order->default_shipment->item_subtotal);

        // shipping subtotals
        $this->assertEquals(10, $order->default_shipment->shipping_gross_subtotal);
        $this->assertEquals(10, $order->default_shipment->shipping_discount_amount);
        $this->assertEquals(0, $order->default_shipment->shipping_subtotal);

        // shipment totals
        $this->assertEquals(110, $order->default_shipment->gross_total);
        $this->assertEquals(0, $order->default_shipment->total);

        // order totals
        $this->assertEquals(100, $order->item_gross_subtotal);
        $this->assertEquals(100, $order->item_discount_amount);
        $this->assertEquals(0, $order->item_subtotal);

        $this->assertEquals(10, $order->shipping_gross_subtotal);
        $this->assertEquals(10, $order->shipping_discount_amount);
        $this->assertEquals(0, $order->shipping_subtotal);

        $this->assertEquals(110, $order->gross_total);
        $this->assertEquals(110, $order->coupon_value);
        $this->assertEquals(0, $order->total);

    }
}
