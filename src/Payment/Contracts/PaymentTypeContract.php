<?php

namespace Wax\Shop\Payment\Contracts;

interface PaymentTypeContract
{
    public function authorize($order, $amount);
    public function capture();
}
