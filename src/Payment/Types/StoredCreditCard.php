<?php

namespace Wax\Shop\Payment\Types;

use Wax\Shop\Models\User\PaymentMethod;
use Wax\Shop\Payment\Contracts\PaymentTypeContract;

class StoredCreditCard extends PaymentMethod implements PaymentTypeContract
{
    public function authorize($amount)
    {
        return true;
    }

    public function capture()
    {
        return true;
    }
}
