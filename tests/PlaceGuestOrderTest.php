<?php

namespace Tests\Shop;

use Illuminate\Support\Facades\Event;
use Tests\Shop\Support\Models\User;
use Tests\Shop\Support\ShopBaseTestCase;
use Tests\Shop\Traits\BuildsPlaceableOrders;
use Tests\Shop\Traits\GeneratesCreditCardPayments;
use Tests\Shop\Traits\SeedsProducts;
use Wax\Shop\Models\Order\Payment;
use Wax\Shop\Models\Order\ShippingRate;
use Wax\Shop\Models\Product;
use Wax\Shop\Payment\Drivers\DummyDriver;
use Wax\Shop\Payment\PaymentTypeFactory;
use Wax\Shop\Services\ShopService;

class PlaceGuestOrderTest extends ShopBaseTestCase
{
    use GeneratesCreditCardPayments,
        BuildsPlaceableOrders,
        SeedsProducts;

    /* @var ShopService $shop */
    protected $shopService;

    /* @var User */
    protected $user;

    protected $product;

    public function setUp()
    {
        parent::setUp();

        $this->seedProducts();

        // events are not tested here, but I want them faked to keep things simple.
        Event::fake();

        config(['wax.shop.payment.stored_payment_driver' => DummyDriver::class]);

        $this->shopService = app()->make(ShopService::class);

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
        $data = $this->generateCreditCardPaymentData();

        $card = PaymentTypeFactory::create('credit_card', $data);

        $payment = $this->shopService->applyPayment($card);

        $placedOrder = $this->shopService->getPlacedOrder();
        $this->assertNotNull($placedOrder);

        $this->assertTrue($order->is($placedOrder));
    }

    public function testItemDataPersists()
    {
        $order = $this->buildPlaceableOrder();

        $item = $order->items->first();
        $this->assertNull($item->getAttributes()['sku']);
        $this->assertNull($item->getAttributes()['name']);
        $this->assertNull($item->getAttributes()['price']);
        $this->assertNull($item->getAttributes()['shipping_flat_rate']);
        $this->assertNull($item->getAttributes()['shipping_enable_rate_lookup']);
        $this->assertNull($item->getAttributes()['shipping_disable_free_shipping']);
        $this->assertNull($item->getAttributes()['shipping_enable_tracking_number']);
        $this->assertNull($item->getAttributes()['dim_l']);
        $this->assertNull($item->getAttributes()['dim_w']);
        $this->assertNull($item->getAttributes()['dim_h']);
        $this->assertNull($item->getAttributes()['weight']);


        $this->assertTrue($order->place());
        $order->refresh();

        $item = $order->items->first();

        $this->assertNotNull($item->getAttributes()['sku']);
        $this->assertNotNull($item->getAttributes()['name']);
        $this->assertNotNull($item->getAttributes()['price']);
        $this->assertNotNull($item->getAttributes()['shipping_flat_rate']);
        $this->assertNotNull($item->getAttributes()['shipping_enable_rate_lookup']);
        $this->assertNotNull($item->getAttributes()['shipping_disable_free_shipping']);
        $this->assertNotNull($item->getAttributes()['shipping_enable_tracking_number']);
        $this->assertNotNull($item->getAttributes()['dim_l']);
        $this->assertNotNull($item->getAttributes()['dim_w']);
        $this->assertNotNull($item->getAttributes()['dim_h']);
        $this->assertNotNull($item->getAttributes()['weight']);

        $this->assertEquals($this->product['sku'], $item->getAttributes()['sku']);
        $this->assertEquals($this->product['name'], $item->getAttributes()['name']);
        $this->assertEquals($this->product['price'], $item->getAttributes()['price']);
        $this->assertEquals($this->product['shipping_flat_rate'], $item->getAttributes()['shipping_flat_rate']);
        $this->assertEquals(
            (bool)$this->product['shipping_enable_rate_lookup'],
            (bool)$item->getAttributes()['shipping_enable_rate_lookup']
        );
        $this->assertEquals(
            (bool)$this->product['shipping_disable_free_shipping'],
            (bool)$item->getAttributes()['shipping_disable_free_shipping']
        );
        $this->assertEquals(
            (bool)$this->product['shipping_enable_tracking_number'],
            (bool)$item->getAttributes()['shipping_enable_tracking_number']
        );
        $this->assertEquals($this->product['dim_l'], $item->getAttributes()['dim_l']);
        $this->assertEquals($this->product['dim_w'], $item->getAttributes()['dim_w']);
        $this->assertEquals($this->product['dim_h'], $item->getAttributes()['dim_h']);
        $this->assertEquals($this->product['weight'], $item->getAttributes()['weight']);
    }

    public function testItemOptionsPersist()
    {
        $product = $this->products['withOptions'];
        $options = $product->options->mapWithKeys(function ($option) {
            return [$option->id => $option->values->random()->id];
        })->all();

        $this->shopService->addOrderItem($product->id, 1, $options);
        $this->setShippingAddress();
        $this->shopService->setShippingService(factory(ShippingRate::class)->create());
        $this->shopService->calculateTax();

        $order = $this->shopService->getActiveOrder();

        // pay the balance due (simple cash-like payment)
        $order->payments()->save(factory(Payment::class)->create([
            'amount' => $order->balance_due
        ]));

        $order->place();
        $order->refresh();

        $item = $order->items->first();

        $this->assertEquals(count($options), $item->options->count());

        foreach ($options as $optionId => $valueId) {
            $productOption = $product->options->where('id', $optionId)->first();
            $productOptionValue = $productOption->values->where('id', $valueId)->first();

            $itemOption = $item->options->where('option_id', $optionId)->first();

            $this->assertEquals($productOption->name, $itemOption->option);
            $this->assertEquals($productOptionValue->name, $itemOption->value);
        }
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

    public function testOrderSequenceMinimumValue()
    {
        config(['wax.shop.misc.minimum_order_sequence' => 12345]);

        $order = $this->buildPlaceableOrder();
        $this->assertTrue($order->place());
        $order->refresh();

        $this->assertEquals(12345, $order->sequence);
        $this->assertEquals(12345, $order->default_shipment->sequence);

        $order = $this->buildPlaceableOrder();
        $this->assertTrue($order->place());
        $order->refresh();

        $this->assertEquals(12346, $order->sequence);
        $this->assertEquals(12346, $order->default_shipment->sequence);
    }
}
