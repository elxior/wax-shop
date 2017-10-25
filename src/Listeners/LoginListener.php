<?php

namespace App\Shop\Listeners;

use App\Shop\Models\Order;
use App\User;
use Illuminate\Auth\Events\Login;

class LoginListener
{
    /**
     * Handle the event.
     *
     * @param  Login  $event
     * @return void
     */
    public function handle(Login $event)
    {
        $this->deleteSavedIncompleteOrdersWithEmptyCart($event);

        $activeOrder = Order::where('session_id', session()->getId())->first();

        if ($activeOrder) {
            if (($activeOrder->item_count == 0) && ($this->getSavedOrders($event)->count() > 0)) {
                $activeOrder->delete();
                return true;
            }

            if ($activeOrder->item_count > 0) {
                $this->getSavedOrders($event)
                    ->get()
                    ->each
                    ->delete();
            }

            $activeOrder->session_id = null;
            $activeOrder->user_id = $event->user->id;
            $activeOrder->save();

            $this->setOrderDefaultAddresses($activeOrder, $event->user);
        }
    }

    protected function getSavedOrders(Login $event)
    {
        return Order::where('user_id', $event->user->id)
            ->whereNull('placed_at');
    }

    protected function deleteSavedIncompleteOrdersWithEmptyCart(Login $event)
    {
        Order::where('user_id', $event->user->id)
            ->whereNull('placed_at')
            ->each(function ($order) {
                if ($order->item_count === 0) {
                    $order->delete();
                }
            });
    }

    protected function setOrderDefaultAddresses(Order $order, User $user)
    {
        $shippingAddress = $user->addresses()->where('default_shipping', true)->first();
        if (!$shippingAddress) {
            return;
        }

        $address = collect($shippingAddress->toArray())
            ->only([
                'firstname',
                'lastname',
                'phone',
                'email',
                'company',
                'address1',
                'address2',
                'city',
                'state',
                'zip',
                'country',
            ])->toArray();

        foreach ($order->shipments as $shipment) {
            if ($shipment->isAddressSet()) {
                continue;
            }

            $shipment->update($address);
        }
    }
}
