<?php

namespace Wax\Shop\Payment\Validators;

use Omnipay\Common\CreditCard;
use Omnipay\Common\Helper;
use Illuminate\Support\MessageBag;
use Wax\Shop\Models\Order\Payment;
use Wax\Shop\Validators\AbstractValidator;

class OrderPaymentParser extends AbstractValidator
{
    protected $payment;

    public function __construct(Payment $payment)
    {
        $this->payment = $payment;
    }

    public function passes() : bool
    {
        $this->messages = new MessageBag;

        if (!in_array($this->payment->response, ['AUTHORIZED', 'CAPTURED'])) {
            $this->errors()->add(
                'payment',
                __('shop::payment.payment_error', ['message' => $this->payment->error])
            );
        }

        return $this->messages->isEmpty();
    }
}
