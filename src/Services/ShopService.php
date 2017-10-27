<?php

namespace Wax\Shop\Services;

use Wax\Shop\Models\Order;
use Wax\Shop\Models\Order\ShippingRate;
use Wax\Shop\Repositories\OrderRepository;
use Wax\Shop\Validators\OrderItemQuantityValidator;
use Illuminate\Support\Facades\Auth;

class ShopService
{
    protected $orderRepo;

    public function __construct(OrderRepository $orderRepo)
    {
        $this->orderRepo = $orderRepo;
    }

    public function getActiveOrder(): Order
    {
        return $this->orderRepo->getActive();
    }

    public function getCompletedOrder(): Order
    {
        return $this->orderRepo->getCompleted();
    }

    public function getOrderById($id): Order
    {
        return $this->orderRepo->getById($id);
    }

    public function addOrderItem(int $productId, int $quantity = 1, array $options = [], array $customizations = [])
    {
        $this->getActiveOrder()
            ->default_shipment
            ->addItem($productId, $quantity, $options, $customizations);
    }

    public function updateOrderItemQuantity(int $itemId, int $quantity)
    {
        $this->getActiveOrder()
            ->default_shipment
            ->updateItemQuantity($itemId, $quantity);
    }

    public function deleteOrderItem(int $itemId)
    {
        $this->getActiveOrder()
            ->default_shipment
            ->deleteItem($itemId);
    }

    public function orderHasProduct(int $productId, array $options = null, array $customizations = null): bool
    {
        return $this->getActiveOrder()->hasProduct($productId, $options, $customizations);
    }

    public function userOwnsProduct(int $productId, array $options = null, array $customizations = null): bool
    {
        if (!Auth::check()) {
            return false;
        }

        return Order::where('user_id', Auth::user()->id)
            ->whereNotNull('placed_at')
            ->get()
            ->filter(function ($order) use ($productId, $options, $customizations) {
                /* @var Order $order */
                return $order->hasProduct($productId, $options, $customizations);
            })
            ->isNotEmpty();
    }

    public function applyCoupon(string $code) : bool
    {
        return $this->getActiveOrder()->applyCoupon($code);
    }

    public function setShippingService(ShippingRate $rate)
    {
        return $this->getActiveOrder()
            ->default_shipment
            ->setShippingService($rate);
    }

    public function setShippingAddress(
        string $firstName,
        string $lastName,
        string $company,
        string $email,
        string $phone,
        string $address1,
        string $address2,
        string $city,
        string $state,
        string $zip,
        string $countryCode
    ) {
        return $this->getActiveOrder()
            ->default_shipment
            ->setAddress(
                $firstName,
                $lastName,
                $company,
                $email,
                $phone,
                $address1,
                $address2,
                $city,
                $state,
                $zip,
                $countryCode
            );
    }

    public function calculateTax()
    {
        $this->getActiveOrder()->calculateTax();
    }

    public function commitTax()
    {
        $this->getActiveOrder()->commitTax();
    }

    public function validateOrderPayable()
    {
        return $this->getActiveOrder()->validatePayable();
    }
}
