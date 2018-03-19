<?php

namespace Wax\Shop\Payment\Contracts;

use App\User;
use Wax\Shop\Exceptions\ValidationException;
use Wax\Shop\Models\Order;
use Wax\Shop\Models\Order\Payment;
use Wax\Shop\Models\User\PaymentMethod;

interface StoredPaymentDriverContract
{
    /**
     * Create a payment profile at the gateway and return a PaymentMethod model.
     *
     * @param array $data
     * @return PaymentMethod
     * @throws ValidationException
     */
    public function createCard($data) : PaymentMethod;

    /**
     * Update an existing PaymentMethod. The gateway communication may be implemented as a Delete & Create instead of
     * a pure Update as necessary.
     *
     * @param array $data
     * @param PaymentMethod $originalPaymentMethod
     * @return PaymentMethod
     * @throws ValidationException
     */
    public function updateCard($data, PaymentMethod $originalPaymentMethod) : PaymentMethod;

    /**
     * Delete a PaymentMethod along with the corresponding gateway payment profile.
     *
     * @param PaymentMethod $paymentMethod
     * @throws ValidationException
     */
    public function deleteCard(PaymentMethod $paymentMethod);

    /**
     * Create a 'purchase' transaction (authorize and capture) for an order. If an amount is not provided, it should
     * default to the balance due on the order
     *
     * @param Order $order
     * @param PaymentMethod $paymentMethod
     * @param float|null $amount
     * @return Payment
     */
    public function purchase(Order $order, PaymentMethod $paymentMethod, float $amount) : Payment;

    /**
     * Set the user to use on all activities through this driver. Return $this for chainability.
     *
     * @param User $user
     * @return $this
     */
    public function setUser(User $user) : StoredPaymentDriverContract;
}
