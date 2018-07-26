<?php

namespace Tests\Shop\Payment;

use Illuminate\Support\Facades\Lang;
use Tests\Shop\Support\ShopBaseTestCase;
use Tests\Shop\Support\Models\User;
use Tests\Shop\Traits\GeneratesPaymentMethods;
use Wax\Shop\Payment\Drivers\DummyDriver;
use Wax\Shop\Payment\Repositories\PaymentMethodRepository;
use Wax\Shop\Services\ShopService;

class PaymentApiTest extends ShopBaseTestCase
{
    use GeneratesPaymentMethods;

    /* @var \Faker\Generator */
    protected $faker;

    /* @var PaymentMethodRepository */
    protected $repo;

    /* @var ShopService $shop */
    protected $shopService;

    protected $paymentMethodUnauthorizedResponse = 'Payment unauthorized response string';
    protected $setShippingAddressUnauthorizedResponse = 'Shipping Address unauthorized response string';

    public function setUp()
    {
        parent::setUp();

        config(['wax.shop.payment.stored_payment_driver' => DummyDriver::class]);

        $this->repo = app()->make(PaymentMethodRepository::class);

        // fallback for un-mocked strings
        Lang::shouldReceive('getFromJson')
            ->andReturn('');

        Lang::shouldReceive('trans')
            ->andReturn('');

        Lang::shouldReceive('setLocale')
            ->andReturn('');

        // mock error response strings
        Lang::shouldReceive('getFromJson')
            ->with('shop::payment.make_payment_unauthorized', [], null)
            ->andReturn($this->paymentMethodUnauthorizedResponse);

        Lang::shouldReceive('getFromJson')
            ->with('shop::payment.set_shipping_address', [], null)
            ->andReturn($this->setShippingAddressUnauthorizedResponse);

    }

    public function testMakePaymentUnauthorized()
    {
        $user = factory(User::class)->create();

        $this->be($user);
        $data = $this->generatePaymentMethodData();
        $paymentMethod = $this->repo->create($data);

        // the user who owns the PaymentMethod should get a validation error (order is not placeable)
        $response = $this->actingAs($user)
            ->json('POST', route('shop::api.paymentMethods.pay', ['paymentmethod' => $paymentMethod]));
        $response->assertStatus(422);

        // a different user should get "Unauthorized"
        $user2 = factory(User::class)->create();
        $response = $this->actingAs($user2)
            ->json('POST', route('shop::api.paymentMethods.pay', ['paymentmethod' => $paymentMethod]));
        $response->assertStatus(403)
            ->assertJson(['_error' => [__($this->paymentMethodUnauthorizedResponse)]]);
    }

    public function testUseBillingAddressUnauthorized()
    {
        $user = factory(User::class)->create();

        $this->be($user);
        $data = $this->generatePaymentMethodData();
        $paymentMethod = $this->repo->create($data);

        // the user who owns the PaymentMethod can use its address
        $response = $this->actingAs($user)
            ->json(
                'POST',
                route('shop::api.paymentMethods.setShippingAddress', ['paymentmethod' => $paymentMethod])
            );
        $response->assertStatus(200);

        // a different user should get "Unauthorized"
        $user2 = factory(User::class)->create();
        $response = $this->actingAs($user2)
            ->json(
                'POST',
                route('shop::api.paymentMethods.setShippingAddress', ['paymentmethod' => $paymentMethod])
            );
        $response->assertStatus(403)
            ->assertJson(['_error' => [__($this->setShippingAddressUnauthorizedResponse)]]);
    }

}
