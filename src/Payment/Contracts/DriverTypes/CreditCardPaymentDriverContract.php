<?php

namespace Wax\Shop\Payment\Contracts\DriverTypes;

use Omnipay\Common\CreditCard;
use Wax\Shop\Models\Order;
use Wax\Shop\Models\Order\Payment;

interface CreditCardPaymentDriverContract
{
    /**
     * Create a 'purchase' transaction (authorize and capture) for an order. If an amount is not provided, it should
     * default to the balance due on the order
     *
     * @param Order $order
     * @param CreditCard $card
     * @param float $amount
     * @return Payment
     * @throws \Exception
     */
    public function purchase(Order $order, CreditCard $card, float $amount) : Payment;

    public function authorize(Order $order, CreditCard $card, float $amount) : Payment;

    public function capture(Order $order, Payment $payment) : Payment;
}
