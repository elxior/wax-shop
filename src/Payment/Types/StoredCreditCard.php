<?php

namespace Wax\Shop\Payment\Types;

use Wax\Shop\Models\Order\Payment;
use Wax\Shop\Models\User\PaymentMethod;
use Wax\Shop\Payment\Contracts\PaymentTypeContract;

class StoredCreditCard implements PaymentTypeContract
{

    /* @var \Wax\Shop\Payment\Repositories\PaymentMethodRepository */
    protected $paymentMethodRepo;

    /* @var PaymentMethod */
    protected $paymentMethod;

    public function authorize($order, $amount) : Payment
    {
        return $this->paymentMethodRepo->makePayment($order, $this->paymentMethod, $amount = null);
    }

    public function capture(Payment $payment)
    {
        return true;
    }

    public function loadData($data)
    {
        if($data['id']) {
            $this->paymentMethod = PaymentMethod::find($data['id']);
        } else {
            $this->paymentMethod = $this->paymentMethodRepo->createCard($data);
        }
    }
}
