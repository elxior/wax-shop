<?php

namespace Wax\Shop\Policies;

use App\User;
use Wax\Shop\Models\Order;
use Wax\Core\Repositories\AuthorizationRepository;

class OrderPolicy
{
    public function get(User $user, Order $order)
    {
        $authRepo = new AuthorizationRepository;

        $privilege = $authRepo->getPrivilege('Orders');
        if ($authRepo->userHasPrivilege($user, $privilege)) {
            return true;
        }

        $privilege = $authRepo->getPrivilege('Superuser');
        if ($authRepo->userHasPrivilege($user, $privilege)) {
            return true;
        }

        return $user->id === $order->user_id;
    }
}
