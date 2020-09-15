<?php

namespace Wax\Shop\Services;

use Illuminate\Support\Facades\Auth;
use Omnipay\Common\CreditCard;
use Wax\Shop\Exceptions\ValidationException;
use Wax\Shop\Models\Order;
use Wax\Shop\Models\Order\Payment;
use Wax\Shop\Models\Order\ShippingRate;
use Wax\Shop\Models\User\PaymentMethod;
use Wax\Shop\Payment\Contracts\PaymentTypeContract;
use Wax\Shop\Payment\Repositories\PaymentMethodRepository;
use Wax\Shop\Payment\Validators\OrderPaymentParser;
use Wax\Shop\Repositories\OrderRepository;
use Wax\Shop\Validators\OrderPayableValidator;
use Wax\Shop\Validators\OrderPlaceableValidator;
use Wax\Shop\Validators\OrderProcessableValidator;

class ShopService
{
    protected $orderRepo;

    protected $paymentMethodRepo;

    public function __construct(OrderRepository $orderRepo)
    {
        $this->orderRepo = $orderRepo;
    }

    public function getActiveOrder(): Order
    {
        return $this->orderRepo->getActive();
    }

    public function getPlacedOrder(): ?Order
    {
        return $this->orderRepo->getPlaced();
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

        return $this->orderRepo->getOrderHistory()
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

    public function removeCoupon()
    {
        $this->getActiveOrder()->removeCoupon();
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

    public function validateOrderPayable() : bool
    {
        return $this->getActiveOrder()->validatePayable();
    }

    public function validateOrderPlaceable() : bool
    {
        return $this->getActiveOrder()->validatePlaceable();
    }

    public function applyPayment(PaymentTypeContract $paymentType, $amount = null)
    {
        $order = $this->getActiveOrder();

        if (!$order->validatePayable()) {
            return false;
        }

        if (is_null($amount)) {
            $amount = $order->balance_due;
        }

        // don't allow payments GREATER than the balance due
        $amount = min($amount, $order->balance_due);

        if ($amount <= 0) {
            throw new \Exception('Invalid payment amount');
        }

        if (config('wax.shop.payment.auth_capture')) {
            $payment = $paymentType->purchase($order, $amount);
        } else {
            $payment = $paymentType->authorize($order, $amount);
        }
        $order->payments()->save($payment);

        // Catch payment errors and convert them to a validation exception/message bag
        (new OrderPaymentParser($payment))->validate();

        $order->place();

        return $payment;
    }

    /**
     * Make a payment using a stored payment / token billing profile. As a side-effect, if the payment causes the order
     * to be 'placeable', the order will be flagged as `placed` and trigger the OrderPlacedEvent event.
     *
     * @param PaymentMethod $paymentMethod
     *
     * @return Payment
     * @throws ValidationException
     */
    public function makeStoredPayment(PaymentMethod $paymentMethod) : Payment
    {
        $order = $this->getActiveOrder();

        (new OrderPayableValidator($order))->validate();

        $paymentRepo = app()->make(PaymentMethodRepository::class);
        $payment = $paymentRepo->makePayment($order, $paymentMethod, $order->balance_due);

        // Catch payment errors and convert them to a validation exception/message bag
        (new OrderPaymentParser($payment))->validate();

        $order->place();

        return $payment;
    }

    /**
     * Place an order. This would be used when the order is completely set up and payments/discounts have been
     * applied to bring the balance due to $0.00.
     *
     * @return bool
     * @throws ValidationException
     */
    public function placeOrder() : bool
    {
        $order = $this->getActiveOrder();

        (new OrderPlaceableValidator($order))->validate();

        return $order->place();
    }

    /**
     * Process an already-placed order.  This would be used when the site is configured to do prior auth capture
     * or to process a pending P.O. order at a later date.
     *
     * @return bool
     * @throws ValidationException
     */
    public function processOrder($order) : bool
    {
        (new OrderProcessableValidator($order))->validate();

        return $order->process();
    }
}
