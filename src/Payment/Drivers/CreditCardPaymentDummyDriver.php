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
use Wax\Shop\Payment\Contracts\DriverTypes\CreditCardPaymentDriverContract;
// use Wax\Shop\Payment\Types\CreditCard;
use Wax\Shop\Payment\Validators\AuthorizeNetCim\ExceptionParser;
use Wax\Shop\Payment\Validators\AuthorizeNetCim\PaymentProfileResponseParser;
use Wax\Shop\Payment\Validators\CreditCardPreValidator;

class CreditCardPaymentDummyDriver implements CreditCardPaymentDriverContract
{
    /**
     * Create a 'purchase' transaction (authorize and capture) for an order. If an amount is not provided, it should
     * default to the balance due on the order
     *
     * @param Order $order
     * @param CreditCard $card
     * @param float $amount
     * @return Payment
     * @throws \Exception
     */
    public function purchase(Order $order, CreditCard $card, float $amount) : Payment
    {
        return new Payment([
            'type' => 'credit_card',
            'authorized_at' => Carbon::now(),
            'captured_at' => Carbon::now(),
            'account' => $card->getNumberMasked(),
            'error' => 'The payment was successful.',
            'response' => 'CAPTURED',
            'amount' => $amount,
            'firstname' => $card->getFirstName(),
            'lastname' => $card->getLastName(),
            'address1' => $card->getBillingAddress1(),
            'zip' => $card->getBillingPostcode(),
        ]);
    }

    /**
     * Create an 'authorize' transaction (without capture) for an order. If an amount is not provided, it should
     * default to the balance due on the order
     *
     * @param Order $order
     * @param CreditCard $card
     * @param float $amount
     * @return Payment
     * @throws \Exception
     */
    public function authorize(Order $order, CreditCard $card, float $amount) : Payment
    {
        return new Payment([
            'type' => 'credit_card',
            'authorized_at' => Carbon::now(),
            'account' => $card->getNumberMasked(),
            'error' => 'The payment was authorized.',
            'response' => 'AUTHORIZED',
            'amount' => $amount,
            'firstname' => $card->getFirstName(),
            'lastname' => $card->getLastName(),
            'address1' => $card->getBillingAddress1(),
            'zip' => $card->getBillingPostcode(),
        ]);
    }

    public function capture(Order $order, CreditCard $card, float $amount) : Payment
    {
        return true;
    }
}
