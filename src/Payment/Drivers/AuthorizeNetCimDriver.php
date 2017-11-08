<?php

namespace Wax\Shop\Payment\Drivers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\UnauthorizedException;
use Omnipay\Common\CreditCard;
use Omnipay\Omnipay;
use Wax\Shop\Exceptions\ValidationException;
use Wax\Shop\Models\User\PaymentMethod;
use Wax\Shop\Payment\Contracts\StoredPaymentDriverContract;
use Wax\Shop\Payment\Validators\AuthorizeNetCim\PaymentProfileResponseParser;
use Wax\Shop\Payment\Validators\CreditCardPreValidator;

class AuthorizeNetCimDriver implements StoredPaymentDriverContract
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

    /**
     * Create a payment profile at the gateway and return a PaymentMethod model.
     *
     * @param array $data
     * @return PaymentMethod
     * @throws ValidationException
     */
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

        (new PaymentProfileResponseParser($response))
            ->validate();

        if (!empty($response->getCustomerProfileId())) {
            $this->user->payment_profile_id = $response->getCustomerProfileId();
            $this->user->save();
        }

        $paymentModel = config('wax.shop.models.payment_method');
        return new $paymentModel([
            'payment_profile_id' => $response->getCustomerPaymentProfileId(),
            'brand' => $requestData['card']->getBrand(),
            'masked_card_number' => substr($data['cardNumber'], -4),
            'expiration_date' => $data['expMonth'].'/'.$data['expYear'],
            'firstname' => $data['firstName'],
            'lastname' => $data['lastName'],
            'address' => $data['address'],
            'zip' => $data['zip'],
        ]);
    }

    /**
     * Update an existing PaymentMethod. The gateway communication may be implemented as a Delete & Create instead of
     * a pure Update.
     *
     * @param array $data
     * @param PaymentMethod $originalPaymentMethod
     * @return PaymentMethod
     * @throws ValidationException
     */
    public function updateCard($data, PaymentMethod $originalPaymentMethod)
    {
        $newPaymentMethod = $this->createCard($data);

        $this->deleteCard($originalPaymentMethod);

        return $newPaymentMethod;
    }

    /**
     * Delete a PaymentMethod along with the corresponding gateway payment profile.
     *
     * @param PaymentMethod $paymentMethod
     * @throws ValidationException
     */
    public function deleteCard(PaymentMethod $paymentMethod)
    {
        $requestData = [
            'customerId' => $this->user->id,
            'customerProfileId' => $this->user->payment_profile_id,
            'customerPaymentProfileId' => $paymentMethod->payment_profile_id,
        ];

        $response = $this->gateway
            ->deleteCard($requestData)
            ->send();

        (new PaymentProfileResponseParser($response))
            ->validate();

        $paymentMethod->delete();
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

    protected function buildCardReference(PaymentMethod $paymentMethod)
    {
        return json_encode([
            'customerProfileId' => $this->user->payment_profile_id,
            'customerPaymentProfileId' => $paymentMethod->payment_profile_id,
        ]);
    }
}
