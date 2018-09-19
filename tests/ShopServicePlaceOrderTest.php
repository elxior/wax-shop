<?php

namespace Tests\Shop;

use Illuminate\Support\Facades\Event;
use Tests\Shop\Traits\BuildsPlaceableOrders;
use Tests\Shop\Support\ShopBaseTestCase;
use Wax\Shop\Exceptions\ValidationException;
use Wax\Shop\Models\Product;
use Wax\Shop\Models\Order\ShippingRate;
use Wax\Shop\Services\ShopService;

class ShopServicePlaceOrderTest extends ShopBaseTestCase
{
    use BuildsPlaceableOrders;

    /* @var \Wax\Shop\Services\ShopService */
    protected $shopService;

    public function setUp()
    {
        parent::setUp();

        Event::fake();

        $this->shopService = app()->make(ShopService::class);
    }

    public function testPlaceOrderSuccess()
    {
        $order = $this->buildPlaceableOrder();

        $result = $this->shopService->placeOrder();
        $this->assertTrue($result);

        $placedOrder = $this->shopService->getPlacedOrder();

        $this->assertTrue($placedOrder->is($order));
    }

    public function testPlaceOrderFailsWithEmptyCart()
    {
        // set up the order
        $this->setShippingAddress();
        $this->shopService->calculateTax();

        try {
            $this->shopService->placeOrder();
        } catch (ValidationException $e) {
            // proceed
        }

        $this->assertInstanceOf(ValidationException::class, $e);
        $messages = $e->errors()['general'];

        $this->assertContains('Your cart is empty.', $messages);
    }

    public function testPlaceOrderFailsWithMissingShipping()
    {
        // set up the order
        $product = factory(Product::class)->create(['price' => 10, 'shipping_enable_rate_lookup' => true]);
        $this->shopService->addOrderItem($product->id);

        try {
            $this->shopService->placeOrder();
        } catch (ValidationException $e) {
            // proceed
        }

        $this->assertInstanceOf(ValidationException::class, $e);
        $messages = $e->errors()['general'];

        $this->assertContains('Please ensure all required shipping information has been provided.', $messages);
    }

    public function testPlaceOrderFailsWithMissingTax()
    {
        // set up the order
        $product = factory(Product::class)->create(['price' => 10]);
        $this->shopService->addOrderItem($product->id);

        $this->setShippingAddress();
        $this->shopService->setShippingService(factory(ShippingRate::class)->create());
        //$this->shopService->calculateTax();

        try {
            $this->shopService->placeOrder();
        } catch (ValidationException $e) {
            // proceed
        }

        $this->assertInstanceOf(ValidationException::class, $e);
        $messages = $e->errors()['general'];

        $this->assertContains('Tax has not yet been calculated for the order.', $messages);
    }

    public function testPlaceOrderFailsWithBalanceDue()
    {
        // set up the order
        $product = factory(Product::class)->create(['price' => 10]);
        $this->shopService->addOrderItem($product->id);
        $this->setShippingAddress();
        $this->shopService->setShippingService(factory(ShippingRate::class)->create());
        $this->shopService->calculateTax();

        $order = $this->shopService->getActiveOrder();
        $this->assertGreaterThan(0, $order->balance_due);

        try {
            $this->shopService->placeOrder();
        } catch (ValidationException $e) {
            // proceed
        }

        $this->assertInstanceOf(ValidationException::class, $e);
        $messages = $e->errors()['general'];

        $this->assertContains('The order has a balance due of $'.$order->balance_due, $messages);
    }
}
