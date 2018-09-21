<?php

namespace Wax\Shop\Payment\Types;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Wax\Shop\Models\Order\Payment;
use Wax\Shop\Payment\Contracts\PaymentTypeContract;

class PurchaseOrder implements PaymentTypeContract
{
    /* @var string $po */
    protected $po;

    public function authorize($order, $amount) : Payment
    {
        return new Payment([
            'type' => 'PurchaseOrder',
            'authorized_at' => Carbon::now(),
            'account' => $this->po,
            'error' => 'The payment was authorized.',
            'response' => 'AUTHORIZED',
            'amount' => $amount,
            'firstname' => Auth::user()->firstname,
            'lastname' => Auth::user()->lastname,
            'address1' => $order->default_shipment->address1,
            'zip' => $order->default_shipment->zip,
        ]);
    }

    public function capture(Payment $payment)
    {
        return true;
    }

    public function loadData($data)
    {
        $this->po = $data['po-number'] ?? '';
    }
}
