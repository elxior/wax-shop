<?php

namespace Wax\Shop\Payment\Types;

use Wax\Shop\Payment\Contracts\PaymentTypeContract;

class PurchaseOrder implements PaymentTypeContract
{

    public function __construct(array $data)
    {
        $this->card = $this->create($data);
    }

    protected function getDriver()
    {
        return app()->make(config('wax.shop.payment.purchase_order_payment_driver'));
    }

    public function create($data)
    {
        return $this->getDriver()->createPurchaseOrder($data);
    }

    public function authorize($amount)
    {
        return true;
    }

    public function capture()
    {
        return true;
    }
}
