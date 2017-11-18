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
        $order = Order::mine()
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
        return Order::mine()
            ->placed()
            ->orderBy('placed_at', 'desc')
            ->first();
    }

    public function getOrderHistory()
    {
        return Order::mine()
            ->placed()
            ->orderBy('placed_at', 'desc')
            ->get();
    }

    public function getById($orderId) : Order
    {
        $order = Order::where('id', $orderId)->firstOrFail();

        if (Gate::denies('get-order', $order)) {
            abort(403);
        }

        return $order;
    }

    protected function create() : Order
    {
        $order = new Order;
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
