<?php

namespace Wax\Shop\Payment\Types;

use Carbon\Carbon;
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
        return $this->getDriver()->authorize($order, $this->cc, $amount);
    }

    public function purchase($order, $amount) : Payment
    {
        return $this->getDriver()->purchase($order, $this->cc, $amount);
    }

    public function capture(Payment $payment)
    {
        if (empty($payment->account)) {
            return false;
        }

        $payment->captured_at = Carbon::now();
        $payment->response = 'CAPTURED';
        $payment->save();

        return true;
    }

    public function getCardData($data)
    {
        $name = explode(' ', $data['name']);
        $firstname = $name[0];
        $lastname = implode(' ', array_slice($name, 1));

        $data['expiry'] = str_replace(' ', '', $data['expiry']);
        if (strpos($data['expiry'], '/') === false) {
            $data['expiry'] = substr($data['expiry'], 0, 2) . '/' . substr($data['expiry'], -1 * (strlen($data['expiry']) - 2));
        }
        $expDate = array_map(function ($n) use ($data) {
            return preg_replace("/[^0-9]/", "", substr('00' . $n, -2));
        }, explode('/', $data['expiry']));

        return [
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
    }

    public function loadData($data)
    {
        $card = new OmnipayCommonCreditCard($this->getCardData($data));
        (new CreditCardPreValidator($card))->validate();

        $this->cc = $card;
    }
}
