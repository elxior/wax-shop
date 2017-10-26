<?php

namespace App\Shop\Repositories;

use App\Shop\Events\OrderChanged\CouponChangedEvent;
use App\Shop\Models\Order;
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
            $order->coupon->delete();
            $order->calculateCouponValue();
            $order->refresh();

            event(new CouponChangedEvent($order));
        }

        return $order;
    }

    public function getPlaced() : Order
    {
        return Order::mine()
            ->placed()
            ->orderBy('placed_at', 'desc')
            ->first();
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

        $order->save();

        return $order;
    }
}
