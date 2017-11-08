<?php

namespace Wax\Shop\Policies;

use App\User;
use Wax\Shop\Models\User\PaymentMethod;
use Illuminate\Auth\Access\HandlesAuthorization;

class PaymentMethodPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can update the $paymentMethod.
     *
     * @param  \App\User  $user
     * @param  PaymentMethod  $paymentMethod
     * @return mixed
     */
    public function update(User $user, PaymentMethod $paymentMethod)
    {
        return $user->id === $paymentMethod->user_id;
    }

    /**
     * Determine whether the user can delete the $paymentMethod.
     *
     * @param  \App\User  $user
     * @param  PaymentMethod  $paymentMethod
     * @return mixed
     */
    public function delete(User $user, PaymentMethod $paymentMethod)
    {
        return $user->id === $paymentMethod->user_id;
    }
}
