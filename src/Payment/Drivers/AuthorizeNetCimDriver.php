<?php

namespace Wax\Shop\Payment\Drivers;

use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\UnauthorizedException;
use Omnipay\Common\CreditCard;
use Omnipay\Common\Message\AbstractResponse;
use Omnipay\Omnipay;
use Wax\Shop\Exceptions\ValidationException;
use Wax\Shop\Models\Order;
use Wax\Shop\Models\Order\Payment;
use Wax\Shop\Models\User\PaymentMethod;
use Wax\Shop\Payment\Contracts\StoredPaymentDriverContract;
use Wax\Shop\Payment\Validators\AuthorizeNetCim\ExceptionParser;
use Wax\Shop\Payment\Validators\AuthorizeNetCim\PaymentProfileResponseParser;
use Wax\Shop\Payment\Validators\CreditCardPreValidator;

class AuthorizeNetCimDriver implements StoredPaymentDriverContract
{
    protected $gateway;
    protected $user;

    public function __construct()
    {
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

    public function setUser(User $user) : StoredPaymentDriverContract
    {
        $this->user = $user;

        return $this;
    }

    protected function getUser()
    {
        if (is_null($this->user)) {
            if (!Auth::check()) {
                throw new \Exception;
            }
            $this->user = Auth::user();
        }
        
        return $this->user;
    }

    /**
     * Create a payment profile at the gateway and return a PaymentMethod model.
     *
     * @param array $data
     * @return PaymentMethod
     * @throws ValidationException
     */
    public function createCard($data) : PaymentMethod
    {
        $requestData = [
            'customerId' => $this->getUser()->id,
            'email' => $this->getUser()->email,
            'card' => $this->prepareCreditCardData($data),
        ];

        if ($this->getUser()->payment_profile_id) {
            $requestData['customerProfileId'] = $this->getUser()->payment_profile_id;
        }

        try {
            $response = $this->gateway
                ->createCard($requestData)
                ->send();
        } catch (\Exception $e) {
            (new ExceptionParser($e))->validate();
        }


        (new PaymentProfileResponseParser($response))
            ->validate();

        if (!empty($response->getCustomerProfileId())) {
            $this->getUser()->payment_profile_id = $response->getCustomerProfileId();
            $this->getUser()->save();
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
     * a pure Update as necessary.
     *
     * @param array $data
     * @param PaymentMethod $originalPaymentMethod
     * @return PaymentMethod
     * @throws ValidationException
     */
    public function updateCard($data, PaymentMethod $originalPaymentMethod) : PaymentMethod
    {
        try {
            $newPaymentMethod = $this->createCard($data);
            $this->deleteCard($originalPaymentMethod);
        } catch (\Exception $e) {
            (new ExceptionParser($e))->validate();
        }

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
            'customerId' => $this->getUser()->id,
            'customerProfileId' => $this->getUser()->payment_profile_id,
            'customerPaymentProfileId' => $paymentMethod->payment_profile_id,
        ];

        try {
            $response = $this->gateway
                ->deleteCard($requestData)
                ->send();
        } catch (\Exception $e) {
            (new ExceptionParser($e))->validate();
        }

        (new PaymentProfileResponseParser($response))
            ->validate();
    }

    /**
     * Create a 'purchase' transaction (authorize and capture) for an order. If an amount is not provided, it should
     * default to the balance due on the order
     *
     * @param Order $order
     * @param PaymentMethod $paymentMethod
     * @param float $amount
     * @return Payment
     * @throws \Exception
     */
    public function purchase(Order $order, PaymentMethod $paymentMethod, float $amount) : Payment
    {
        $requestData = [
            'cardReference' => $this->buildCardReference($paymentMethod),
            'amount' => $amount,
        ];

        try {
            $response = $this->gateway
                ->purchase($requestData)
                ->send();
        } catch (\Exception $e) {
            // An exception here is an error generated from by OmniPay, not the payment gateway.
            return $this->buildPaymentError($paymentMethod, $amount, $e->getMessage());
        }

        return $this->parseTransactionResponse($response, $paymentMethod);
    }

    protected function parseTransactionResponse(AbstractResponse $response, PaymentMethod $paymentMethod) : Payment
    {
        $transactionType = (string)$response->getRequest()->getData()->transactionRequest->transactionType;
        $amount = (float)$response->getRequest()->getData()->transactionRequest->amount;

        // If this was a priorAuthCapture, you'd want to amend the existing payment here instead of creating one.
        $payment = new Payment([
            'type' => 'Credit Card',
            'account' => (string)$response->getData()->transactionResponse->accountNumber,
            'brand' => (string)$response->getData()->transactionResponse->accountType,
            'error' => $response->getMessage(),
            'response' => null, // AUTHORIZED, CAPTURED, DECLINED, ERROR
            'auth_code' => $response->getAuthorizationCode(),
            'transaction_ref' => $response->getTransactionReference(),
            'amount' => $amount,
            'firstname' => $paymentMethod->firstname,
            'lastname' => $paymentMethod->lastname,
            'address1' => $paymentMethod->address,
            'zip' => $paymentMethod->zip,
        ]);

        if ($response->isSuccessful()) {
            switch ($transactionType) {
                case 'authCaptureTransaction':
                    $payment->response = 'CAPTURED';
                    $payment->authorized_at = Carbon::now();
                    $payment->captured_at = Carbon::now();
                    break;

                case 'priorAuthCaptureTransaction':
                    $payment->response = 'CAPTURED';
                    $payment->captured_at = Carbon::now();
                    break;

                case 'authOnlyTransaction':
                    $payment->response = 'AUTHORIZED';
                    $payment->authorized_at = Carbon::now();
                    break;
            }
        } else {
            switch ($response->getCode()) {
                case $response::TRANSACTION_RESULT_CODE_DECLINED:
                    $payment->response = 'DECLINED';
                    break;

                default:
                    $payment->response = 'ERROR';
                    break;
            }
        }

        return $payment;
    }

    protected function buildPaymentError(PaymentMethod $paymentMethod, float $amount, string $message) : Payment
    {
        return new Payment([
            'type' => 'Credit Card',
            'account' => $paymentMethod->account_number,
            'brand' => $paymentMethod->brand,
            'error' => $message,
            'response' => 'ERROR',
            'amount' => $amount,
            'firstname' => $paymentMethod->firstname,
            'lastname' => $paymentMethod->lastname,
            'address1' => $paymentMethod->address,
            'zip' => $paymentMethod->zip,
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

    protected function buildCardReference(PaymentMethod $paymentMethod)
    {
        return json_encode([
            'customerProfileId' => $this->getUser()->payment_profile_id,
            'customerPaymentProfileId' => $paymentMethod->payment_profile_id,
        ]);
    }
}
