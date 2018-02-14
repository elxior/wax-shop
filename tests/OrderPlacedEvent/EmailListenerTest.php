<?php

namespace Tests\Shop\Payment;

use Faker\Factory;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Mail;
use Tests\Shop\Support\ShopBaseTestCase;
use Tests\Shop\Traits\BuildsPlaceableOrders;
use Wax\Shop\Events\OrderPlacedEvent;
use Wax\Shop\Listeners\OrderPlaced\EmailListener;
use Wax\Shop\Mail\OrderPlaced;
use Wax\Shop\Services\ShopService;

class EmailListenerTest extends ShopBaseTestCase
{
    use BuildsPlaceableOrders;

    /* @var ShopService */
    protected $shopService;

    /* @var \Faker\Generator */
    protected $faker;

    protected $testSubject = 'teh subject';

    public function setUp()
    {
        parent::setUp();

        $this->faker = Factory::create();

        // the listener will be triggered manually
        Event::fake();

        // don't try to actually send mail
        Mail::fake();

        $this->shopService = app()->make(ShopService::class);

        // Mock the translation for email subject line
        Lang::shouldReceive('getFromJson')
            ->with('shop::mail.order_placed_subject', [], null)
            ->andReturn($this->testSubject);
    }

    public function testMailableObject()
    {
        $emailAddress = $this->faker->safeEmail;

        $order = $this->buildPlaceableOrder();
        $order->email = $emailAddress;
        $order->save();

        $order->place();

        $mailable = (new OrderPlaced($order))->build();

        $this->assertTrue($mailable->hasFrom($this->testMailFrom, config('app.name')));
        $this->assertEquals('shop::mail.order-placed', $mailable->view);
        $this->assertEquals($this->testSubject, $mailable->subject);
    }

    public function testEmailSent()
    {
        $emailAddress = $this->faker->safeEmail;

        $order = $this->buildPlaceableOrder();
        $order->email = $emailAddress;
        $order->save();

        $order->place();

        $listener = new EmailListener();
        $listener->handle(new OrderPlacedEvent($order->fresh()));

        Mail::assertQueued(OrderPlaced::class, function ($mail) use ($emailAddress) {
            return $mail->hasTo($emailAddress);
        });

        Mail::assertQueued(OrderPlaced::class, function ($mail) {
            return $mail->hasTo('test1@example.org')
                && $mail->hasTo('test2@example.org');
        });
    }
}
