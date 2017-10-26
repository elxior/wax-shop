<?php

namespace Wax\Shop\Policies;

use Wax\Shop\Models\Order;
use App\User;

class OrderPolicy
{
    public function get(User $user, Order $order)
    {
        return $user->id === $order->user_id;
    }
}
