<?php

namespace Tests\Shop\Payment;

use App\User;
use Carbon\Carbon;
use Omnipay\AuthorizeNet\CIMGateway;
use Omnipay\AuthorizeNet\Message\CIMAbstractResponse;
use Tests\Shop\Support\ShopBaseTestCase;
use Wax\Shop\Models\Order\Payment;
use Wax\Shop\Models\Product;
use Wax\Shop\Payment\Drivers\AuthorizeNetCimDriver;
use Wax\Shop\Services\ShopService;

class AuthnetCimTest extends ShopBaseTestCase
{
    protected $gatewayMock;
    protected $driver;
    protected $user;

    public function setUp()
    {
        parent::setUp();

        $this->gatewayMock = $this->createMock(CIMGateway::class);
        $this->user = factory(User::class)->make();
        $this->driver = new AuthorizeNetCimDriver($this->gatewayMock);
        $this->driver->setUser($this->user);
    }

    public function testCreateCreditCard()
    {
        $paymentProfileId = 'f43qefr43qw2';
        $customerProfileId = 'f4532ty54376y';
        $ccResponse = $this->createMock(CIMAbstractResponse::class);
        $ccResponse->method('isSuccessful')
            ->willReturn(true);
        $ccResponse->expects($this->any())
            ->method('getCustomerProfileId')
            ->willReturn($customerProfileId);
        $ccResponse->method('getCustomerPaymentProfileId')
            ->willReturn($paymentProfileId);

        $sender = new class($ccResponse) {
            public function __construct($ccResponse)
            {
                $this->ccResponse = $ccResponse;
            }
            public function send()
            {
                return $this->ccResponse;
            }
        };

        $this->gatewayMock->expects($this->any())
            ->method('createCard')
            ->willReturn($sender);

        $data = [
            'firstName' => 'John',
            'lastName' => 'Smith',
            'address' => "123 Colonly Way",
            'zip' => "00001",
            'cardNumber' => 4111111111111111,
            'expMonth' => Carbon::now()->addMonth(1)->format('m'),
            'expYear' => Carbon::now()->addYear(1)->format('Y'),
            'cvc' => 700,
        ];

        $paymentMethod = $this->driver->createCard($data);

        $this->assertEquals($paymentProfileId, $paymentMethod->payment_profile_id);
        $this->assertEquals($this->user->payment_profile_id, $customerProfileId);
    }
}
