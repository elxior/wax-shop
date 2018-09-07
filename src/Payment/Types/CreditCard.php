<?php

namespace Wax\Shop\Payment\Types;

use Omnipay\Common\CreditCard as OmnipayCommonCreditCard;
use Wax\Shop\Payment\Contracts\PaymentTypeContract;

class CreditCard extends OmnipayCommonCreditCard implements PaymentTypeContract
{
    protected function getDriver()
    {
        return app()->make(config('wax.shop.payment.credit_card_payment_driver'));
    }

    public function authorize($order, $amount)
    {
        if (config('wax.shop.payment.prior_auth_capture')) {
            return $this->getDriver()->authorize($order, $this, $amount);
        } else {
            return $this->getDriver()->purchase($order, $this, $amount);
        }
    }

    public function capture()
    {
        return true;
    }
}
