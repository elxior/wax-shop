<?php

namespace Tests\Shop;

use App\User;
use Illuminate\Support\Facades\Event;
use Tests\Shop\Support\ShopBaseTestCase;
use Tests\Shop\Traits\BuildsPlaceableOrders;
use Wax\Shop\Models\Product;
use Wax\Shop\Models\Order\ShippingRate;
use Wax\Shop\Services\ShopService;

class CheckoutApiTest extends ShopBaseTestCase
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
        $user = factory(User::class)->create();

        $this->be($user);

        $this->buildPlaceableOrder();

        $response = $this->actingAs($user)
            ->json(
                'POST',
                route('shop::api.place-order')
            );

        $response->assertStatus(200);
        $this->assertEquals(true, $response->json());
    }

    public function testPlaceOrderFailsWithBalanceDue()
    {
        $user = factory(User::class)->create();

        $this->be($user);

        $product = factory(Product::class)->create(['price' => 10]);
        $this->shopService->addOrderItem($product->id);
        $this->setShippingAddress();
        $this->shopService->setShippingService(factory(ShippingRate::class)->create());
        $this->shopService->calculateTax();

        $order = $this->shopService->getActiveOrder();

        $response = $this->actingAs($user)
            ->json(
                'POST',
                route('shop::api.place-order')
            );

        $response->assertStatus(422);

        $errors = $response->json()['errors']['general'];
        $this->assertContains('The order has a balance due of $'.$order->balance_due, $errors);
    }
}
