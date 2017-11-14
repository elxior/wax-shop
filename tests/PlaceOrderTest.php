<?php

namespace Tests\Shop;

use Tests\Shop\Support\ShopBaseTestCase;
use Tests\Shop\Traits\SetsShippingAddress;
use Wax\Shop\Models\Order\Payment;
use Wax\Shop\Models\Order\ShippingRate;
use Wax\Shop\Models\Product;
use Wax\Shop\Services\ShopService;

class PlaceOrderTest extends ShopBaseTestCase
{
    use SetsShippingAddress;

    /* @var ShopService $shop */
    protected $shopService;

    protected $product;

    public function setUp()
    {
        parent::setUp();

        $this->shopService = app()->make(ShopService::class);

        $this->product = factory(Product::class)->create(['price' => 10]);
    }

    public function testGetPlacedOrder()
    {
        $order = $this->buildPlaceableOrder();

        //dump($order->validateHasItems());
        //dump($order->validateShipping());
       // dump($order->validateTax());
        if ($order->balance_due !== 0) {
            dump('not zero');
        }

        dd($order->balance_due);

        $this->assertTrue($order->place());

        $placedOrder = $this->shopService->getPlacedOrder();

        $this->assertTrue($placedOrder->is($order));
    }

    public function testItemDataPersists()
    {
        $order = $this->buildPlaceableOrder();

        $item = $order->items->first();

        $this->assertNotEquals($this->product['sku'], $item->getAttribute('sku'));

        $this->assertTrue($order->place());

        $this->assertEquals($this->product['sku'], $item->getAttribute('sku'));
    }

    public function testShipmentDataPersists()
    {
        $this->assertTrue(false);
    }

    public function testOrderDataPersists()
    {
        $this->assertTrue(false);
    }

    protected function buildPlaceableOrder()
    {
        // set up the order
        $this->shopService->addOrderItem($this->product->id);
        $this->setShippingAddress();
        $this->shopService->setShippingService(factory(ShippingRate::class)->create());
        $this->shopService->calculateTax();

        $order = $this->shopService->getActiveOrder();

        // pay the balance due (simple cash-like payment)
        $order->payments()->save(factory(Payment::class)->create([
            'amount' => $order->balance_due
        ]));

        return $order->fresh();
    }

}
