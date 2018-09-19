<?php

namespace Wax\Shop\Payment\Contracts;

use Wax\Shop\Models\Order\Payment;

interface PaymentTypeContract
{
    public function authorize($order, $amount) : Payment;
    public function capture(Payment $payment);
    public function loadData($data);
}
