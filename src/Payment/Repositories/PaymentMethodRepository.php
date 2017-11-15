<?php

namespace Wax\Shop\Payment\Repositories;

use Illuminate\Support\Facades\Auth;
use Wax\Shop\Models\Order;
use Wax\Shop\Models\User\PaymentMethod;
use Wax\Shop\Payment\Contracts\StoredPaymentDriverContract;
use Wax\Shop\Services\ShopService;

class PaymentMethodRepository
{
    protected $shopService;

    public function __construct(ShopService $shopService)
    {
        $this->shopService = $shopService;
    }

    protected function getDriver() : StoredPaymentDriverContract
    {
        return app()->make(config('wax.shop.payment.stored_payment_driver'));
    }

    public function getAll()
    {
        return Auth::user()->paymentMethods;
    }

    public function create($data) : PaymentMethod
    {
        $paymentMethod = $this->getDriver()->createCard($data);

        Auth::user()->paymentMethods()->save($paymentMethod);
        Auth::user()->refresh();

        return $paymentMethod->fresh();
    }

    public function update($data, PaymentMethod $paymentMethod) : PaymentMethod
    {
        $paymentMethod = $this->getDriver()->updateCard($data, $paymentMethod);

        Auth::user()->paymentMethods()->save($paymentMethod);
        Auth::user()->refresh();

        return $paymentMethod->fresh();
    }

    public function delete(PaymentMethod $paymentMethod)
    {
        $this->getDriver()->deleteCard($paymentMethod);

        $paymentMethod->delete();
        Auth::user()->refresh();
    }

    public function makePayment(Order $order, PaymentMethod $paymentMethod, float $amount = null)
    {
        /**
         * Authorization & validation may have already been checked in the controller or elsewhere, but since
         * we're dealing with payments it's worth the overhead to double-check.
         */
        if (Auth::user()->cant('pay', $paymentMethod)) {
            return false;
        }

        if (!$order->validatePayable()) {
            return false;
        }

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

    public function useAddressForShipping(Order $order, PaymentMethod $paymentMethod)
    {
        $order->shipments->each(function ($shipment) use ($paymentMethod) {
            $shipment->setAddress(
                $paymentMethod->firstname,
                $paymentMethod->lastname,
                '',
                Auth::user()->email,
                '',
                $paymentMethod->address,
                '',
                '',
                '',
                $paymentMethod->zip,
                ''
            );
        });

        $this->shopService->calculateTax();
    }
}
