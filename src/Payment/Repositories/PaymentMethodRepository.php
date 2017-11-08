<?php

namespace Wax\Shop\Payment\Repositories;

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\UnauthorizedException;
use Wax\Shop\Models\Order;
use Wax\Shop\Models\User\PaymentMethod;
use Wax\Shop\Payment\Contracts\StoredPaymentDriverContract;
use Wax\Shop\Payment\Drivers\AuthorizeNetCimDriver;

class PaymentMethodRepository
{
    protected function getDriver() : StoredPaymentDriverContract
    {
        return app()->make(AuthorizeNetCimDriver::class);
    }

    public function getAll()
    {
        return Auth::user()->paymentMethods;
    }

    public function create($data)
    {
        $paymentMethod = $this->getDriver()->createCard($data);

        Auth::user()->paymentMethods()->save($paymentMethod);
    }

    public function update($data, PaymentMethod $paymentMethod)
    {
        $paymentMethod = $this->getDriver()->updateCard($data, $paymentMethod);

        Auth::user()->paymentMethods()->save($paymentMethod);
    }

    public function delete(PaymentMethod $paymentMethod)
    {
        $this->getDriver()->deleteCard($paymentMethod);
    }

    public function makePayment(Order $order, PaymentMethod $paymentMethod, float $amount = null)
    {
        if (is_null($amount)) {
            $amount = $order->balance_due;
        }

        // don't allow payments GREATER than the balance due
        $amount = min($amount, $order->balance_due);

        if ($amount <= 0) {
            throw new \Exception('Invalid payment amount');
        }

        $payment = $this->getDriver()->purchase($order, $paymentMethod, $amount);
        $order->payments()->save($payment);

        return $payment;
    }
}
