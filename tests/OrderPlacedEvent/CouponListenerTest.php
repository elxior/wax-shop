<?php

namespace Tests\Shop\Payment;

use Illuminate\Support\Facades\Event;
use Tests\Shop\Support\ShopBaseTestCase;
use Tests\Shop\Traits\SetsShippingAddress;
use Wax\Shop\Events\OrderPlacedEvent;
use Wax\Shop\Listeners\OrderPlaced\CouponListener;
use Wax\Shop\Models\Coupon;
use Wax\Shop\Models\Order\Payment;
use Wax\Shop\Models\Product;
use Wax\Shop\Services\ShopService;

class CouponListenerTestTest extends ShopBaseTestCase
{
    Use SetsShippingAddress;

    /* @var ShopService */
    protected $shopService;

    public function setUp()
    {
        parent::setUp();

        // the listener will be triggered manually
        Event::fake();

        $this->shopService = app()->make(ShopService::class);
    }

    public function testOneTimeUse()
    {
        $coupon = factory(Coupon::class)
            ->create([
                'dollars' => 1,
                'minimum_order' => 10,
                'one_time' => true
            ]);

        // add product to cart and apply coupon
        $product = factory(Product::class)->create(['price' => 10]);
        $this->shopService->addOrderItem($product->id);
        $this->assertTrue($this->shopService->applyCoupon($coupon->code));

        // prepare the order
        $this->setShippingAddress();
        $this->shopService->calculateTax();

        // pay the balance due (simple cash-like payment)
        $order = $this->shopService->getActiveOrder();
        $order->payments()->save(factory(Payment::class)->create([
            'amount' => $order->balance_due
        ]));
        $order->refresh();

        $order->place();

        $this->assertEquals(1, Coupon::where('code', $coupon->code)->count());

        $listener = new CouponListener();
        $listener->handle(new OrderPlacedEvent($order->fresh()));

        // the order totals still add up
        $this->assertEquals($coupon->dollars, $order->coupon->calculated_value);
        $this->assertEquals($coupon->dollars, $order->coupon_value);
        $this->assertEquals(10, $order->gross_total);
        $this->assertEquals(9, $order->total);

        // The source coupon has been removed
        $this->assertEquals(0, Coupon::where('code', $coupon->code)->count());
    }
}
