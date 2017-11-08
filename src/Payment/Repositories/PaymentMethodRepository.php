<?php

namespace Wax\Shop\Payment\Repositories;

use Illuminate\Support\Facades\Auth;
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
}
