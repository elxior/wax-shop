<?php

namespace Wax\Shop\Payment;

use Wax\Shop\Payment\Contracts\PaymentTypeContract;

class PaymentTypeFactory
{
    public static function create($type, $data) : PaymentTypeContract
    {
        $className = config('wax.shop.payment.types.'.$type);

        $class = new $className;
        $class->loadData($data);

        return $class;
    }

    public static function make($type) : PaymentTypeContract
    {
        $className = config('wax.shop.payment.types.'.$type);

        $class = new $className;

        return $class;
    }
}
