<?php

namespace Wax\Shop\Payment\Types;

use Omnipay\Common\CreditCard as OmnipayCommonCreditCard;
use Wax\Shop\Models\Order\Payment;
use Wax\Shop\Payment\Contracts\PaymentTypeContract;
use Wax\Shop\Payment\Validators\CreditCardPreValidator;

class CreditCard implements PaymentTypeContract
{
    /* @var OmnipayCommonCreditCard $cc */
    protected $cc;

    protected function getDriver()
    {
        return app()->make(config('wax.shop.payment.credit_card_payment_driver'));
    }

    public function authorize($order, $amount) : Payment
    {
        if (config('wax.shop.payment.prior_auth_capture')) {
            return $this->getDriver()->authorize($order, $this->cc, $amount);
        } else {
            return $this->getDriver()->purchase($order, $this->cc, $amount);
        }
    }

    public function capture(Payment $payment)
    {
        return true;
    }

    public function loadData($data)
    {
        $name = explode(' ', $data['name']);
        $firstname = $name[0];
        $lastname = implode(' ', array_slice($name, 1));

        $expDate = $data['expiry'];
        if (strlen($expDate) == 4) {
            $expDate = [
                substr($expDate, 0, 2),
                substr($expDate, -2),
            ];
        } else {
            $expDate = explode(' / ', $expDate);
        }

        $cardData = [
            'number' => str_replace(' ', '', $data['number']),
            'expiryMonth' => $expDate[0],
            'expiryYear' => $expDate[1],
            'cvv' => $data['cvc'],
            'firstName' => $firstname,
            'lastName' => $lastname,
            'billingAddress1' => $data['billing-address'],
            'billingPostcode' => $data['postal-code'],
            'email' => session()->get('guest-email'),
        ];

        $card = new OmnipayCommonCreditCard($cardData);
        (new CreditCardPreValidator($card))->validate();

        $this->cc = $card;
    }
}
