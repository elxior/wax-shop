<?php

namespace App\Shop\Policies;

use App\Shop\Models\Order;
use App\User;

class OrderPolicy
{
    public function get(User $user, Order $order)
    {
        return $user->id === $order->user_id;
    }
}