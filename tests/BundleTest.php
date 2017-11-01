<?php

namespace Tests\Shop;

use Wax\Shop\Models\Bundle;
use Wax\Shop\Models\Product;
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

        $this->shopService->addOrderItem($product1->id);
        $this->shopService->addOrderItem($product2->id);

        $order = $this->shopService->getActiveOrder();
        $this->assertGreaterThan(0, $order->bundle_value);
        $this->assertGreaterThan(0, $order->discount_amount);
    }
}
