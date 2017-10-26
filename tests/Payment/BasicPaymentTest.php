<?php

namespace Tests\Shop\Payment;

use Tests\Shop\ShopBaseTestCase;
use Wax\Shop\Models\Order\Payment;
use Wax\Shop\Models\Product;
use Wax\Shop\Services\ShopService;

class BasicPaymentTest extends ShopBaseTestCase
{
    /* @var ShopService $shop */
    protected $shopService;

    public function setUp()
    {
        parent::setUp();

        $this->shopService = app()->make(ShopService::class);
    }

    public function testBasicPayment()
    {
        $this->shopService->addOrderItem(factory(Product::class)->create(['price' => 10])->id);

        $order = $this->shopService->getActiveOrder();
        $this->assertEquals(10, $order->total);
        $this->assertEquals(10, $order->balance_due);

        $order->payments()->save(factory(Payment::class)->create(['amount' => 10]));

        $order = $this->shopService->getActiveOrder();
        $this->assertEquals(10, $order->total);
        $this->assertEquals(10, $order->payment_total);
        $this->assertEquals(0, $order->balance_due);
    }

    public function testMultiplePayments()
    {
        $this->shopService->addOrderItem(factory(Product::class)->create(['price' => 100])->id);

        $order = $this->shopService->getActiveOrder();
        $this->assertEquals(100, $order->total);
        $this->assertEquals(100, $order->balance_due);

        $order->payments()->save(factory(Payment::class)->create(['amount' => 50]));
        $order = $this->shopService->getActiveOrder();
        $this->assertEquals(100, $order->total);
        $this->assertEquals(50, $order->payment_total);
        $this->assertEquals(50, $order->balance_due);

        $order->payments()->save(factory(Payment::class)->create(['amount' => 50]));
        $order = $this->shopService->getActiveOrder();
        $this->assertEquals(100, $order->total);
        $this->assertEquals(100, $order->payment_total);
        $this->assertEquals(0, $order->balance_due);
    }

    public function testDeclinedPayment()
    {
        $this->shopService->addOrderItem(factory(Product::class)->create(['price' => 10])->id);

        $order = $this->shopService->getActiveOrder();
        $this->assertEquals(10, $order->total);
        $this->assertEquals(10, $order->balance_due);

        $order->payments()->save(factory(Payment::class)->create([
            'authorized_at' => null,
            'amount' => 10,
            'response' => 'DECLINED',
            'error' => 'Your payment is so fake.'
        ]));

        $order = $this->shopService->getActiveOrder();
        $this->assertEquals(1, $order->payments->count());
        $this->assertEquals(10, $order->total);
        $this->assertEquals(0, $order->payment_total);
        $this->assertEquals(10, $order->balance_due);
    }
}
