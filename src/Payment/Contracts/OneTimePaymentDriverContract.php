<?php

namespace Wax\Shop\Payment\Contracts;

use Wax\Shop\Models\Order;
use Wax\Shop\Models\Order\Payment;
use Wax\Shop\Payment\Types\CreditCard;

interface OneTimePaymentDriverContract
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
}
