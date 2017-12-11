<?php

namespace Wax\Shop\Listeners;

use App\User;
use Wax\Shop\Models\Order;
use Wax\Shop\Repositories\OrderRepository;
use Illuminate\Auth\Events\Login;

class LoginListener
{
    protected $orderRepo;

    public function __construct(OrderRepository $orderRepo)
    {
        $this->orderRepo = $orderRepo;
    }

    /**
     * Handle the event.
     *
     * @param  Login  $event
     * @return void
     */
    public function handle(Login $event)
    {
        $this->deleteSavedIncompleteOrdersWithEmptyCart($event);

        $activeOrder = $this->orderRepo->getOrderModel()->active()->where('session_id', session()->getId())->first();
        if ($activeOrder) {
            $this->handleActiveOrder($activeOrder, $event);
        }

        // switch all orders matching the session id to have a user_id instead
        $this->orderRepo->getOrderModel()->where('session_id', session()->getId())
            ->each(function ($order) use ($event) {
                $order->session_id = null;
                $order->user_id = $event->user->id;
                $order->save();
            });
    }

    protected function handleActiveOrder(Order $activeOrder, Login $event)
    {
        // if the session cart is empty but the user has saved order(s), delete the session order
        if (($activeOrder->item_count == 0) && ($this->getSavedOrders($event)->count() > 0)) {
            $activeOrder->delete();
            return;
        }

        // if the session cart has items, it replaces any saved incomplete orders
        if ($activeOrder->item_count > 0) {
            $this->getSavedOrders($event)
                ->each
                ->delete();
        }

        $this->setOrderDefaultAddresses($activeOrder, $event->user);
    }

    protected function getSavedOrders(Login $event)
    {
        return $this->orderRepo->getUnplacedOrdersByUserId($event->user->id);
    }

    protected function deleteSavedIncompleteOrdersWithEmptyCart(Login $event)
    {
        $this->orderRepo->getUnplacedOrdersByUserId($event->user->id)
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
