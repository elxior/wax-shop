<?php

namespace Wax\Shop\Repositories;

use Wax\Shop\Models\Order;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Session;

class OrderRepository
{
    public function getActive() : Order
    {
        $order = $this->getNewOrder()
            ->mine()
            ->active()
            ->first() ?? $this->create();

        if ($order->coupon && !$order->coupon->validate()) {
            $order->removeCoupon();
            $order->refresh();
        }

        return $order;
    }

    public function getPlaced() : ?Order
    {
        return $this->getNewOrder()->mine()
            ->placed()
            ->orderBy('placed_at', 'desc')
            ->first();
    }

    public function getOrderHistory()
    {
        return $this->getNewOrder()->mine()
            ->placed()
            ->orderBy('placed_at', 'desc')
            ->get();
    }

    public function getById($orderId) : Order
    {
        $order = $this->getNewOrder()->where('id', $orderId)->firstOrFail();

        if (Gate::denies('get-order', $order)) {
            abort(403);
        }

        return $order;
    }

    public function getUnplacedOrdersByUserId($userId)
    {
        return $this->getNewOrder()->where('user_id', $userId)
            ->whereNull('placed_at')
            ->get();
    }

    public function getNewOrder()
    {
        $orderClass = $this->getOrderClass();
        return (new $orderClass);
    }

    public function getOrderClass()
    {
        return config('wax.shop.models.order');
    }

    protected function create() : Order
    {
        $order = $this->getNewOrder();
        if (Auth::check()) {
            $order->user_id = Auth::user()->id;
        } else {
            $order->session_id = Session::getId();
        }

        $order->ip_address = request()->ip();

        $order->save();

        return $order;
    }
}
