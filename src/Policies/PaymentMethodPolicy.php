<?php

namespace Wax\Shop\Policies;

use Wax\Core\Eloquent\Models\User;
use Wax\Shop\Models\User\PaymentMethod;
use Illuminate\Auth\Access\HandlesAuthorization;

class PaymentMethodPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the $paymentMethod.
     *
     * @param  User  $user
     * @param  PaymentMethod  $paymentMethod
     * @return mixed
     */
    public function view(User $user, PaymentMethod $paymentMethod)
    {
        return $user->id === (int)$paymentMethod->user_id;
    }

    /**
     * Determine whether the user can update the $paymentMethod.
     *
     * @param  User  $user
     * @param  PaymentMethod  $paymentMethod
     * @return mixed
     */
    public function update(User $user, PaymentMethod $paymentMethod)
    {
        return $user->id === (int)$paymentMethod->user_id;
    }

    /**
     * Determine whether the user can delete the $paymentMethod.
     *
     * @param  User  $user
     * @param  PaymentMethod  $paymentMethod
     * @return mixed
     */
    public function delete(User $user, PaymentMethod $paymentMethod)
    {
        return $user->id === (int)$paymentMethod->user_id;
    }

    /**
     * Determine whether the user can make a payment using the $paymentMethod.
     *
     * @param  User  $user
     * @param  PaymentMethod  $paymentMethod
     * @return mixed
     */
    public function pay(User $user, PaymentMethod $paymentMethod)
    {
        return $user->id === (int)$paymentMethod->user_id;
    }
}
