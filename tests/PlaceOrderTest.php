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

        $this->assertTrue($order->place());

        $placedOrder = $this->shopService->getPlacedOrder();

        $this->assertTrue($placedOrder->is($order));
    }

    public function testItemDataPersists()
    {
        $order = $this->buildPlaceableOrder();

        $item = $order->items->first();
        $this->assertNotEquals($this->product['sku'], $item->getAttributes()['sku']);
        $this->assertNotEquals($this->product['name'], $item->getAttributes()['name']);
        $this->assertNotEquals($this->product['price'], $item->getAttributes()['price']);

        $this->assertTrue($order->place());
        $order->refresh();

        $item = $order->items->first();
        $this->assertEquals($this->product['sku'], $item->getAttributes()['sku']);
        $this->assertEquals($this->product['name'], $item->getAttributes()['name']);
        $this->assertEquals($this->product['price'], $item->getAttributes()['price']);
    }

    public function testShipmentDataPersists()
    {
        $order = $this->buildPlaceableOrder();
        $this->assertTrue($order->place());
        $order->refresh();

        $this->assertGreaterThan(0, $order->default_shipment->sequence);
    }

    public function testOrderDataPersists()
    {
        $order = $this->buildPlaceableOrder();
        $this->assertTrue($order->place());
        $order->refresh();

        $this->assertNotEmpty($order->email);
        $this->assertGreaterThan(0, $order->getAttributes()['total']);
        $this->assertGreaterThan(0, $order->sequence);
        $this->assertNotNull($order->placed_at);
        $this->assertNotEmpty($order->searchIndex);
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
