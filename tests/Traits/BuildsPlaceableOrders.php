<?php

namespace Tests\Shop\Traits;

use Wax\Shop\Models\Order;
use Wax\Shop\Models\Order\Payment;
use Wax\Shop\Models\Order\ShippingRate;
use Wax\Shop\Models\Product;
use Wax\Shop\Services\ShopService;

trait BuildsPlaceableOrders
{
    use SetsShippingAddress;

    /* @var ShopService */
    protected $shopService;

    /* @var Product */
    protected $product;

    protected function buildPlaceableOrder() : Order
    {
        $this->product = factory(Product::class)->create(['price' => 10]);

        if (!$this->shopService) {
            $this->shopService = app()->make(ShopService::class);
        }

        // set up the order
        $this->shopService->addOrderItem($this->product->id);
        $this->setShippingAddress();
        $this->shopService->setShippingService(factory(ShippingRate::class)->create());
        $this->shopService->calculateTax();

        $order = $this->shopService->getActiveOrder();

        // pay the balance due (simple cash-like payment)
        $order->payments()->save(factory(Payment::class)->create([
            'amount' => $order->balance_due
        ]));

        return $order->fresh();
    }
}
