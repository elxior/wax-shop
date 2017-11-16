<?php

namespace Tests\Shop;

use Illuminate\Support\Facades\Event;
use Tests\Shop\Support\Models\User;
use Tests\Shop\Support\ShopBaseTestCase;
use Tests\Shop\Traits\BuildsPlaceableOrders;
use Tests\Shop\Traits\GeneratesPaymentMethods;
use Wax\Shop\Models\Order\ShippingRate;
use Wax\Shop\Models\Product;
use Wax\Shop\Payment\Drivers\DummyDriver;
use Wax\Shop\Payment\Repositories\PaymentMethodRepository;
use Wax\Shop\Services\ShopService;

class PlaceOrderTest extends ShopBaseTestCase
{
    use GeneratesPaymentMethods,
        BuildsPlaceableOrders;

    /* @var ShopService $shop */
    protected $shopService;

    /* @var PaymentMethodRepository */
    protected $storedPaymentRepo;

    /* @var User */
    protected $user;

    protected $product;

    public function setUp()
    {
        parent::setUp();

        // events are not tested here, but I want them faked to keep things simple.
        Event::fake();

        config(['wax.shop.payment.stored_payment_driver' => DummyDriver::class]);

        $this->shopService = app()->make(ShopService::class);

        $this->storedPaymentRepo = app()->make(PaymentMethodRepository::class);

        $this->product = factory(Product::class)->create(['price' => 10]);

        $this->user = factory(User::class)->create();
    }

    public function testGetPlacedOrder()
    {
        $order = $this->buildPlaceableOrder();

        $this->assertTrue($order->place());

        $placedOrder = $this->shopService->getPlacedOrder();

        $this->assertTrue($placedOrder->is($order));
    }

    public function testPayingBalanceDueCausesOrderPlaced()
    {
        $this->be($this->user);

        // set up the order
        $this->shopService->addOrderItem($this->product->id);
        $this->setShippingAddress();
        $this->shopService->setShippingService(factory(ShippingRate::class)->create());
        $this->shopService->calculateTax();

        $order = $this->shopService->getActiveOrder();

        // make the payment
        $data = $this->generatePaymentMethodData();
        $paymentMethod = $this->storedPaymentRepo->create($data);
        $this->shopService->makeStoredPayment($paymentMethod);

        $placedOrder = $this->shopService->getPlacedOrder();
        $this->assertNotNull($placedOrder);

        $this->assertTrue($order->is($placedOrder));
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
}
