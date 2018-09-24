<?php

namespace Wax\Shop\Payment;

use Wax\Shop\Payment\Contracts\PaymentTypeContract;

class PaymentTypeFactory
{
    public static function create($type, $data = null) : PaymentTypeContract
    {
        $className = config('wax.shop.payment.types.'.$type);

        $class = new $className;

        if (!is_null($data)) {
            $class->loadData($data);
        }

        return $class;
    }
}
