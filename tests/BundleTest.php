<?php

namespace Tests\Shop;

use App\User;
use Tests\Shop\Support\ShopBaseTestCase;
use Wax\Shop\Models\Bundle;
use Wax\Shop\Models\Product;
use Wax\Shop\Repositories\ProductRepository;
use Wax\Shop\Services\ShopService;

class BundleTest extends ShopBaseTestCase
{

    /* @var ShopService $shop */
    protected $shopService;

    public function setUp()
    {
        parent::setUp();

        $this->shopService = app()->make(ShopService::class);
    }

    public function testBundleAppliesToOrder()
    {
        $product1 = factory(Product::class)->create();
        $product2 = factory(Product::class)->create();

        $this->assertEmpty($product1->bundles);
        $this->assertEmpty($product2->bundles);

        $bundle = Bundle::create([
            'name' => 'Test Bundle',
            'percent' => 10,
        ]);
        $bundle->products()->saveMany([$product1, $product2]);

        $product1->refresh();
        $product2->refresh();
        $this->assertNotEmpty($product1->bundles);
        $this->assertNotEmpty($product2->bundles);
try {
        $this->shopService->addOrderItem($product1->id);
} catch (\Exception $e) {
    dd($e);
}
        $this->shopService->addOrderItem($product2->id);

        $order = $this->shopService->getActiveOrder();

        /**
         * Check the discount math
         */
        $product1Discount = round($product1->price *.1, 2);
        $product2Discount = round($product2->price *.1, 2);

        $cartTotal = $product1->price + $product2->price;
        $discountTotal = $product1Discount + $product2Discount;

        $this->assertEquals($product1Discount, $order->items[0]->discount_amount);
        $this->assertEquals($product2Discount, $order->items[1]->discount_amount);

        $this->assertEquals($discountTotal, $order->bundle_value);
        $this->assertEquals($discountTotal, $order->discount_amount);

        $this->assertEquals($cartTotal, $order->gross_total);
        $this->assertEquals($cartTotal - $discountTotal, $order->total);

        /**
         * Check that the active bundles show on the order
         */
        $this->assertNotEmpty($order->bundles);
    }

    public function testOrderItemsSuggestBundles()
    {
        $product1 = factory(Product::class)->create();
        $product2 = factory(Product::class)->create();

        $this->assertEmpty($product1->bundles);
        $this->assertEmpty($product2->bundles);

        $bundle = Bundle::create([
            'name' => 'Test Bundle',
            'percent' => 10,
        ]);
        $bundle->products()->saveMany([$product1, $product2]);

        $this->shopService->addOrderItem($product1->id);

        $order = $this->shopService->getActiveOrder();

        $item = $order->items->first();
        $this->assertNotEmpty($item->bundles);
        $this->assertEquals(2, $item->bundles->first()->products->count());
    }

    public function testCartApiSuggestsBundles()
    {
        $product1 = factory(Product::class)->create();
        $product2 = factory(Product::class)->create();

        $bundle = Bundle::create([
            'name' => 'Test Bundle',
            'percent' => 10,
        ]);
        $bundle->products()->saveMany([$product1, $product2]);

        $response = $this->json('POST', route('shop::api.cart.store'), [
            'product_id' => $product1->id,
            'quantity' => 1
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'items' => [
                    0 => [
                        'id' => $product1->id,
                        'bundles' => [
                            0 => [
                                'id' => $bundle->id,
                                'products' => [
                                    0 => ['id' => $product1->id],
                                    1 => ['id' => $product2->id],
                                ]
                            ]
                        ]
                    ]
                ]
            ]);
    }

    public function testRepoGetSuggestsBundles()
    {
        $product1 = factory(Product::class)->create();
        $product2 = factory(Product::class)->create();

        $bundle = Bundle::create([
            'name' => 'Test Bundle',
            'percent' => 10,
        ]);
        $bundle->products()->saveMany([$product1, $product2]);

        $repo = app()->make(ProductRepository::class);
        $product = $repo->get($product1->id);

        $this->assertNotEmpty($product->bundles);
        $this->assertEquals(2, $product->bundles->first()->products->count());
    }

    public function testCartApiListsActiveBundles()
    {
        $product1 = factory(Product::class)->create();
        $product2 = factory(Product::class)->create();

        $this->assertEmpty($product1->bundles);
        $this->assertEmpty($product2->bundles);

        $bundle = Bundle::create([
            'name' => 'Test Bundle',
            'percent' => 10,
        ]);
        $bundle->products()->saveMany([$product1, $product2]);

        $user = factory(User::class)->create();

        $this->actingAs($user)
            ->json('POST', route('shop::api.cart.store'), [
                'product_id' => $product1->id,
                'quantity' => 1
            ]);

        $response = $this->actingAs($user)
            ->json('POST', route('shop::api.cart.store'), [
                'product_id' => $product2->id,
                'quantity' => 1
            ]);

        $product1Discount = round($product1->price *.1, 2);
        $product2Discount = round($product2->price *.1, 2);

        $discountTotal = round($product1Discount + $product2Discount, 2);

        $response->assertStatus(200)
            ->assertJson([
                'bundles' => [
                    0 => ['id' => 1]
                ],
                'items' => [
                    0 => [
                        'id' => $product1->id,
                        'bundle_id' => 1,
                        'discount_amount' => $product1Discount,
                    ],
                    1 => [
                        'id' => $product2->id,
                        'bundle_id' => 1,
                        'discount_amount' => $product2Discount,
                    ]
                ],
                'bundle_value' => $discountTotal,
                'discount_amount' => $discountTotal,
            ]);
    }
}
