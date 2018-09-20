<?php

namespace Wax\Shop\Payment\Drivers;

use Wax\Core\Eloquent\Models\User;
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
use Wax\Shop\Payment\Contracts\DriverTypes\StoredCreditCardDriverContract;
use Wax\Shop\Payment\Validators\AuthorizeNetCim\ExceptionParser;
use Wax\Shop\Payment\Validators\AuthorizeNetCim\PaymentProfileResponseParser;
use Wax\Shop\Payment\Validators\CreditCardPreValidator;

class DummyDriver implements StoredCreditCardDriverContract
{
    protected $user;

    public function setUser(User $user) : StoredCreditCardDriverContract
    {
        return $this;
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
        $paymentModel = config('wax.shop.models.payment_method');
        return new $paymentModel([
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
     * @param PaymentMethod $paymentModel
     * @return PaymentMethod
     * @throws ValidationException
     */
    public function updateCard($data, PaymentMethod $paymentModel) : PaymentMethod
    {
        $paymentModel->fill([
            'masked_card_number' => substr($data['cardNumber'], -4),
            'expiration_date' => $data['expMonth'].'/'.$data['expYear'],
            'firstname' => $data['firstName'],
            'lastname' => $data['lastName'],
            'address' => $data['address'],
            'zip' => $data['zip'],
        ]);

        $paymentModel->save();

        return $paymentModel;
    }

    /**
     * Delete a PaymentMethod along with the corresponding gateway payment profile.
     *
     * @param PaymentMethod $paymentMethod
     * @throws ValidationException
     */
    public function deleteCard(PaymentMethod $paymentMethod)
    {
        // nothing to do at the gateway, repo handles user relation
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
        return new Payment([
            'type' => 'Dummy',
            'authorized_at' => Carbon::now(),
            'captured_at' => Carbon::now(),
            'account' => $paymentMethod->masked_card_number,
            'error' => 'The payment was successful.',
            'response' => 'CAPTURED',
            'amount' => $amount,
            'firstname' => $paymentMethod->firstname,
            'lastname' => $paymentMethod->lastname,
            'address1' => $paymentMethod->address,
            'zip' => $paymentMethod->zip,
        ]);
    }
}
