<?php

namespace Tests\Shop\Shipping;

use Faker\Factory;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Mail;
use Tests\Shop\Support\ShopBaseTestCase;
use Tests\Shop\Traits\BuildsPlaceableOrders;
use Wax\Core\Support\ConfigurationDatabase;
use Wax\Shop\Mail\OrderShipped;
use Wax\Shop\Models\Order\Payment;
use Wax\Shop\Models\Product;
use Wax\Shop\Services\ShopService;

class ShippedAtTest extends ShopBaseTestCase
{
    use BuildsPlaceableOrders;

    /* @var \Faker\Generator */
    protected $faker;

    /* @var ShopService $shop */
    protected $shopService;

    protected $testMailFrom = 'noreply@example.org';
    protected $testSubject = 'teh subject line about order shipped or something';

    public function setUp()
    {
        parent::setUp();

        $this->faker = Factory::create();

        // Don't fire events
        Event::fake();

        // don't try to actually send mail
        Mail::fake();

        $this->shopService = app()->make(ShopService::class);

        app()->bind(ConfigurationDatabase::class, function() {
            $double = \Mockery::mock(ConfigurationDatabase::class);
            $double->shouldReceive('get')
                ->with('WEBSITE_MAILFROM')
                ->andReturn($this->testMailFrom);
            return $double;
        });

        // Mock the translation for email subject line
        Lang::shouldReceive('getFromJson')
            ->with('shop::mail.order_shipped_subject', [], null)
            ->andReturn($this->testSubject);
    }

    public function testSetTrackingNumber()
    {
        $order = $this->buildPlaceableOrder();
        $this->assertTrue($order->place());
        $order->refresh();

        $trackingNumber = $this->faker->uuid;

        $order->default_shipment->setTrackingNumber($trackingNumber);
        $order->refresh();

        $this->assertEquals($trackingNumber, $order->default_shipment->tracking_number);
    }

    public function testSetTrackingNumberFlagsShipment()
    {
        $order = $this->buildPlaceableOrder();
        $order->place();
        $order->refresh();

        $this->assertNull($order->default_shipment->shipped_at);

        $trackingNumber = $this->faker->uuid;
        $order->default_shipment->setTrackingNumber($trackingNumber);
        $order->refresh();

        $this->assertNotNull($order->default_shipment->shipped_at);
    }

    public function testFlagAllShipmentsCausesOrderFlagged()
    {
        /**
         * Set up a placeable order with two shipments
         */
        $order = $this->shopService->getActiveOrder();
        $order->shipments()->create([]);
        $order->shipments()->create([]);

        $order = $this->shopService->getActiveOrder();
        $order->shipments[0]->addItem(factory(Product::class)->create(['shipping_enable_rate_lookup' => false])->id);
        $order->shipments[1]->addItem(factory(Product::class)->create(['shipping_enable_rate_lookup' => false])->id);

        $order = $this->shopService->getActiveOrder();
        $this->setShipmentShippingAddress($order->shipments[0]);
        $this->setShipmentShippingAddress($order->shipments[1]);

        $this->shopService->calculateTax();
        $order = $this->shopService->getActiveOrder();

        // pay the balance due (simple cash-like payment)
        $order->payments()->save(factory(Payment::class)->create([
            'amount' => $order->balance_due
        ]));

        $order->place();
        $order->refresh();

        // order is initially NOT flagged `shipped_at`
        $this->assertNull($order->shipped_at);

        // shipping the first shipment does not affect $order->shipped_at
        $trackingNumber = $this->faker->uuid;
        $order->shipments[0]->setTrackingNumber($trackingNumber);
        $order->refresh();
        $this->assertNull($order->shipped_at);

        // shipping the last shipment flags the order
        $trackingNumber = $this->faker->uuid;
        $order->shipments[1]->setTrackingNumber($trackingNumber);
        $order->refresh();
        $this->assertNotNull($order->shipped_at);
    }

    public function testMailableObject()
    {
        $emailAddress = $this->faker->safeEmail;

        $order = $this->buildPlaceableOrder();
        $order->email = $emailAddress;
        $order->save();

        $order->place();

        $trackingNumber = $this->faker->uuid;
        $order->default_shipment->setTrackingNumber($trackingNumber);
        $order->refresh();

        $mailable = (new OrderShipped($order))->build();

        $this->assertTrue($mailable->hasFrom($this->testMailFrom, config('app.name')));
        $this->assertEquals('emails.order-shipped', $mailable->view);
        $this->assertEquals($this->testSubject, $mailable->subject);
    }

    public function testSetTrackingNumberNotifiesCustomer()
    {
        $emailAddress = $this->faker->safeEmail;

        $order = $this->buildPlaceableOrder();
        $order->email = $emailAddress;
        $order->save();

        $order->place();
        $order->refresh();

        $trackingNumber = $this->faker->uuid;
        $order->default_shipment->setTrackingNumber($trackingNumber);

        Mail::assertSent(OrderShipped::class, function ($mail) use ($emailAddress) {
            return $mail->hasTo($emailAddress);
        });
    }
}
