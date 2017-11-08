<?php

namespace Wax\Shop\Payment\Contracts;

use Wax\Shop\Exceptions\ValidationException;
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
    public function createCard($data);

    /**
     * Update an existing PaymentMethod. The gateway communication may be implemented as a Delete & Create instead of
     * a pure Update.
     *
     * @param array $data
     * @param PaymentMethod $originalPaymentMethod
     * @return PaymentMethod
     * @throws ValidationException
     */
    public function updateCard($data, PaymentMethod $originalPaymentMethod);

    /**
     * Delete a PaymentMethod along with the corresponding gateway payment profile.
     *
     * @param PaymentMethod $paymentMethod
     * @throws ValidationException
     */
    public function deleteCard(PaymentMethod $paymentMethod);
}
