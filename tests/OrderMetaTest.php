<?php

namespace Tests\Shop;

use Wax\Shop\Exceptions\ValidationException;
use Wax\Shop\Services\ShopService;
use Tests\Shop\Traits\SeedsProducts;
use Tests\Shop\ShopBaseTestCase;

class OrderMetaTest extends ShopBaseTestCase
{
    use SeedsProducts;

    /* @var ShopService */
    protected $shopService;

    public function setUp()
    {
        parent::setUp();
        $this->seedProducts();

        $this->shopService = app()->make(ShopService::class);
    }

    public function testCartItemCount()
    {
        $this->shopService->addOrderItem($this->products['basic']->id, 2);
        $this->shopService->addOrderItem($this->products['onePerUser']->id, 1);

        $order = $this->shopService->getActiveOrder();
        $this->assertEquals($order->default_shipment->item_count, 2);
    }

    public function testOrderItemCount()
    {
        $order = $this->shopService->getActiveOrder();
        $order->shipments()->create([]);
        $order->shipments()->create([]);
        $order->refresh();

        $order->shipments[0]->addItem($this->products['basic']->id);
        $order->shipments[1]->addItem($this->products['basic']->id, 2);

        $order = $this->shopService->getActiveOrder();
        $this->assertEquals($order->item_count, 2);
    }

    public function testTotalQuantities()
    {
        $order = $this->shopService->getActiveOrder();
        $order->shipments()->create([]);
        $order->shipments()->create([]);
        $order->refresh();

        $order->shipments[0]->addItem($this->products['basic']->id);
        $order->shipments[1]->addItem($this->products['basic']->id, 2);


        $order = $this->shopService->getActiveOrder();
        $this->assertEquals($order->default_shipment->total_quantity, 1);
        $this->assertEquals($order->total_quantity, 3);
    }

    public function testCartSubtotal()
    {
        // add a basic product
        $quantity1 = 1;
        $this->shopService->addOrderItem($this->products['basic']->id, $quantity1, []);

        // add a product with options+modifiers
        $product = $this->products['withOptionModifiers'];
        $options = $product->options->mapWithKeys(function ($option) {
            return [$option->id => $option->values->random()->id];
        })->all();
        $quantity2 = 2;
        $this->shopService->addOrderItem($product->id, $quantity2, $options);

        $order = $this->shopService->getActiveOrder();

        $this->assertEquals(
            $order->default_shipment->item_gross_subtotal,
            (
                ($this->products['basic']->price * $quantity1)
                // 'sum(value_id) * 10' is hard-coded in the seeder for product modifiers
                + (array_sum($options) * 10 * $quantity2)
            )
        );
    }

    public function testOrderSubtotal()
    {
        $order = $this->shopService->getActiveOrder();
        $order->shipments()->create([]);
        $order->shipments()->create([]);
        $order->refresh();

        $order->shipments[0]->addItem($this->products['basic']->id);
        $order->shipments[1]->addItem($this->products['basic']->id, 2);

        $order = $this->shopService->getActiveOrder();
        $this->assertEquals($order->item_gross_subtotal, $this->products['basic']->price * 3);
    }

    public function testCartDiscountableTotal()
    {
        $this->shopService->addOrderItem($this->products['basic']->id);
        $this->shopService->addOrderItem($this->products['notDiscountable']->id, 2);

        $order = $this->shopService->getActiveOrder();

        $this->assertEquals($order->default_shipment->discountable_total, $this->products['basic']->price);
        $this->assertEquals($order->discountable_total, $this->products['basic']->price);
    }
}
