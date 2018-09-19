<?php

namespace Wax\Shop\Payment;

class PaymentTypeFactory {
    static function create($type, $data) {
        $className = config('payment.types.'.$type);

        $paymentType = new $className($data);

        return $paymentType;
    }
}
