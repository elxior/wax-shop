<?php

namespace Wax\Shop\Payment\Drivers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\UnauthorizedException;
use Omnipay\Common\CreditCard;
use Omnipay\Omnipay;
use App\Models\User\PaymentMethod;
use Wax\Shop\Payment\Validators\AuthorizeNetCim\CreateCardResponseValidator;
use Wax\Shop\Payment\Validators\CreditCardPreValidator;

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

        if (empty(config('wax.shop.payment.drivers.authorizenet_cim.api_login_id'))
            || empty(config('wax.shop.payment.drivers.authorizenet_cim.transaction_key'))
        ) {
            throw new \Exception(
                __('shop::payment.driver_not_configured', ['name' => 'Authorize.net CIM'])
            );
        }

        $this->gateway = Omnipay::create('AuthorizeNet_CIM');
        $this->gateway->setApiLoginId(config('wax.shop.payment.drivers.authorizenet_cim.api_login_id'));
        $this->gateway->setTransactionKey(config('wax.shop.payment.drivers.authorizenet_cim.transaction_key'));

        if (config('wax.shop.payment.drivers.authorizenet_cim.developer_mode')) {
            $this->gateway->setDeveloperMode(true);
        } elseif (config('wax.shop.payment.drivers.authorizenet_cim.test_mode')) {
            $this->gateway->setTestMode(true);
        }
    }

    public function createCard($data)
    {
        $requestData = [
            'customerId' => $this->user->id,
            'email' => $this->user->email,
            'card' => $this->prepareCreditCardData($data),
        ];

        if ($this->user->payment_profile_id) {
            $requestData['customerProfileId'] = $this->user->payment_profile_id;
        }

        $response = $this->gateway
            ->createCard($requestData)
            ->send();

        (new CreateCardResponseValidator($response))
            ->validate();

        if (!empty($response->getCustomerProfileId())) {
            $this->user->payment_profile_id = $response->getCustomerProfileId();
            $this->user->save();
        }

        return new PaymentMethod([
            'gateway_payment_profile_id' => $response->getCustomerPaymentProfileId(),
            'masked_card_number' => substr($data['cardNumber'], -4),
            'expiration_date' => $data['expMonth'].'/'.$data['expYear'],
            'firstname' => $data['firstName'],
            'lastname' => $data['lastName'],
            'address' => $data['address'],
            'zip' => $data['zip'],
        ]);
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

        (new CreditCardPreValidator($card))->validate();

        return $card;
    }
}
