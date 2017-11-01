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
    }
}
