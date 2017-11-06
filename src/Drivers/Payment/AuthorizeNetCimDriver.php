<?php

namespace Wax\Shop\Drivers\Payment;

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\UnauthorizedException;
use Omnipay\Common\CreditCard;
use Omnipay\Omnipay;
use Wax\Shop\Validators\CreditCardValidator;

class AuthorizeNetCimDriver
{
    protected $gateway;
    protected $user;

    public function __construct()
    {
        if (!Auth::check()) {
            throw new UnauthorizedException;
        }
        $this->user = Auth::user();

        $this->gateway = Omnipay::create('AuthorizeNet_CIM');
        $this->gateway->setApiLoginId(config('wax.shop.payment.authorizenet_cim.api_login_id'));
        $this->gateway->setTransactionKey(config('wax.shop.payment.authorizenet_cim.transaction_key'));

        if ($this->user->payment_profile_id) {
            $this->gateway->setCustomerId();
        }

        if (config('wax.shop.payment.authorizenet_cim.developer_mode')) {
            $this->gateway->setDeveloperMode(true);
        } elseif (config('wax.shop.payment.authorizenet_cim.test_mode')) {
            $this->gateway->setTestMode(true);
        }
    }

    public function createCard($data)
    {
        $requestData = [
            'card' => $this->prepareCreditCardData($data)
        ];

        $response = $this->gateway->createCard($requestData);
        dd($response);
    }

    protected function prepareCreditCardData($data)
    {
        $card = new CreditCard([
            'firstName' => $data['firstName'],
            'lastName' => $data['lastName'],
            'billingAddress1' => $data['address'],
            'billingPostcode' => $data['zip'],
            'number' => $data['cardNumber'],
            'expiryMonth' => $data['expMonth'],
            'expiryYear' => $data['expYear'],
            'cvv' => $data['cvc'],
        ]);

        (new CreditCardValidator($card))->validate();

        return $card;
    }
}
