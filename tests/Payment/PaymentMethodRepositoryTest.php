<?php

namespace Tests\Shop\Payment;

use Faker\Factory;
use Tests\Shop\Support\ShopBaseTestCase;
use Tests\Shop\Support\Models\User;
use Tests\Shop\Traits\GeneratesPaymentMethods;
use Wax\Shop\Models\Product;
use Wax\Shop\Payment\Drivers\StoredCreditCardDummyDriver;
use Wax\Shop\Payment\Repositories\PaymentMethodRepository;
use Wax\Shop\Services\ShopService;

class PaymentMethodRepositoryTest extends ShopBaseTestCase
{
    use GeneratesPaymentMethods;

    /* @var \Faker\Generator */
    protected $faker;

    /* @var PaymentMethodRepository */
    protected $repo;

    /* @var User */
    protected $user;

    /* @var ShopService $shop */
    protected $shopService;

    public function setUp()
    {
        parent::setUp();

        config(['wax.shop.payment.stored_payment_driver' => StoredCreditCardDummyDriver::class]);

        $this->faker = Factory::create();

        $this->shopService = app()->make(ShopService::class);

        $this->repo = app()->make(PaymentMethodRepository::class);

        $this->user = factory(User::class)->create();
    }

    public function testUseBillingAddressForShipping()
    {
        $this->be($this->user);
        $data = $this->generatePaymentMethodData();
        $paymentMethod = $this->repo->create($data);

        $this->shopService->addOrderItem(factory(Product::class)->create(['price' => 10])->id);

        $order = $this->shopService->getActiveOrder();
        $this->assertNull($order->default_shipment->address1);
        $this->assertNull($order->default_shipment->zip);

        $this->repo->useAddressForShipping($order, $paymentMethod);

        $order = $this->shopService->getActiveOrder();
        $this->assertEquals($data['billingAddress1'], $order->default_shipment->address1);
        $this->assertEquals($data['billingPostcode'], $order->default_shipment->zip);
    }

    public function testCreatePaymentMethod()
    {
        $this->be($this->user);

        $data = $this->generatePaymentMethodData();
        $this->repo->create($data);

        $this->assertNotEmpty($this->repo->getAll());

        $paymentMethod = $this->repo->getAll()->first();

        $this->assertEquals(substr($data['number'], -4), substr($paymentMethod->masked_card_number, -4));
        $this->assertEquals($data['expiryMonth'], $paymentMethod->expiration_date['month']);
        $this->assertEquals($data['expiryYear'], $paymentMethod->expiration_date['year']);
        $this->assertEquals($data['firstName'], $paymentMethod->firstname);
        $this->assertEquals($data['lastName'], $paymentMethod->lastname);
        $this->assertEquals($data['billingAddress1'], $paymentMethod->address);
        $this->assertEquals($data['billingPostcode'], $paymentMethod->zip);
    }

    public function testUpdatePaymentMethod()
    {
        $this->be($this->user);

        $data = $this->generatePaymentMethodData();
        $this->repo->create($data);

        $this->assertEquals(1, $this->repo->getAll()->count());

        $paymentMethod = $this->repo->getAll()->first();
        $this->assertEquals(substr($data['number'], -4), substr($paymentMethod->masked_card_number, -4));

        $newData = $this->generatePaymentMethodData();
        $this->repo->update($newData, $paymentMethod);

        $this->assertEquals(1, $this->repo->getAll()->count());
        $paymentMethod = $this->repo->getAll()->first();

        $this->assertEquals(substr($newData['number'], -4), substr($paymentMethod->masked_card_number, -4));
        $this->assertEquals($newData['expiryMonth'], $paymentMethod->expiration_date['month']);
        $this->assertEquals($newData['expiryYear'], $paymentMethod->expiration_date['year']);
        $this->assertEquals($newData['firstName'], $paymentMethod->firstname);
        $this->assertEquals($newData['lastName'], $paymentMethod->lastname);
        $this->assertEquals($newData['billingAddress1'], $paymentMethod->address);
        $this->assertEquals($newData['billingPostcode'], $paymentMethod->zip);
    }

    public function testDeletePaymentMethod()
    {
        $this->be($this->user);

        $data = $this->generatePaymentMethodData();
        $this->repo->create($data);

        $this->assertEquals(1, $this->repo->getAll()->count());

        $this->repo->delete($this->repo->getAll()->first());

        $this->assertEmpty($this->repo->getAll());
    }
}
