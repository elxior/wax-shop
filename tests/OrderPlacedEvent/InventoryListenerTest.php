<?php

namespace Tests\Shop\Payment;

use Illuminate\Support\Facades\Event;
use Tests\Shop\Support\ShopBaseTestCase;
use Tests\Shop\Traits\BuildsPlaceableOrders;
use Tests\Shop\Traits\SeedsProducts;
use Wax\Shop\Events\OrderPlacedEvent;
use Wax\Shop\Listeners\OrderPlaced\InventoryListener;
use Wax\Shop\Models\Order\Payment;
use Wax\Shop\Models\Order\ShippingRate;
use Wax\Shop\Services\ShopService;

class InventoryListenerTest extends ShopBaseTestCase
{
    use BuildsPlaceableOrders,
        SeedsProducts;

    /* @var ShopService */
    protected $shopService;

    public function setUp()
    {
        parent::setUp();

        config(['wax.shop.inventory.track' => true]);

        // the listener will be triggered manually
        Event::fake();

        $this->seedProducts();

        $this->shopService = app()->make(ShopService::class);
    }

    public function testBasicProductInventory()
    {
        $order = $this->buildPlaceableOrder();

        $product = $order->items->first()->product;
        $product->inventory = 10;
        $product->save();
        $product->refresh();


        $this->assertEquals(10, $order->items->first()->inventory);
        $this->assertEquals(10, $product->inventory);
        $this->assertEquals(10, $product->effective_inventory);

        $order->place();

        $listener = new InventoryListener();
        $listener->handle(new OrderPlacedEvent($order->fresh()));

        $product->refresh();
        $this->assertEquals(9, $product->inventory);
        $this->assertEquals(9, $product->effective_inventory);
    }

    public function testProductModifierInventory()
    {
        $product = $this->products['withOptionModifiers'];

        $options = $product->options->mapWithKeys(function ($option) {
            return [$option->id => $option->values->random()->id];
        })->all();

        // set up the order
        $this->shopService->addOrderItem($product->id, 1, $options);
        $this->setShippingAddress();
        $this->shopService->setShippingService(factory(ShippingRate::class)->create());
        $this->shopService->calculateTax();

        $order = $this->shopService->getActiveOrder();

        $item = $order->items->first();
        $this->assertNotNull($item->modifier);

        $item->modifier->inventory=10;
        $item->modifier->save();

        $item->refresh();
        $this->assertEquals(10, $item->inventory);
        $this->assertEquals(10, $item->modifier->inventory);

        // pay the balance due (simple cash-like payment)
        $order->payments()->save(factory(Payment::class)->create([
            'amount' => $order->balance_due
        ]));

        $order->place();

        $listener = new InventoryListener();
        $listener->handle(new OrderPlacedEvent($order->fresh()));

        $item->refresh();
        $this->assertEquals(9, $item->inventory);
        $this->assertEquals(9, $item->modifier->inventory);
    }

    public function testProductModifierNullInventory()
    {
        $product = $this->products['withOptionModifiers'];

        $product->inventory = 10;
        $product->save();
        $product->refresh();

        $options = $product->options->mapWithKeys(function ($option) {
            return [$option->id => $option->values->random()->id];
        })->all();

        // set up the order
        $this->shopService->addOrderItem($product->id, 1, $options);
        $this->setShippingAddress();
        $this->shopService->setShippingService(factory(ShippingRate::class)->create());
        $this->shopService->calculateTax();

        $order = $this->shopService->getActiveOrder();

        $item = $order->items->first();
        $this->assertNotNull($item->modifier);
        $this->assertNull($item->modifier->getAttributes()['inventory']);

        $item->refresh();
        $this->assertEquals(10, $item->inventory);
        $this->assertEquals(10, $item->modifier->inventory);

        // pay the balance due (simple cash-like payment)
        $order->payments()->save(factory(Payment::class)->create([
            'amount' => $order->balance_due
        ]));

        $order->place();

        $listener = new InventoryListener();
        $listener->handle(new OrderPlacedEvent($order->fresh()));

        $item->refresh();
        $this->assertEquals(9, $item->product->inventory);
        $this->assertEquals(9, $item->inventory);
        $this->assertNull($item->modifier->getAttributes()['inventory']);
    }

    public function testBasicInventoryWithoutTracking()
    {
        config(['wax.shop.inventory.track' => false]);

        $order = $this->buildPlaceableOrder();

        $product = $order->items->first()->product;
        $product->inventory = 10;
        $product->save();
        $product->refresh();


        $this->assertEquals(10, $order->items->first()->inventory);
        $this->assertEquals(10, $product->inventory);
        $this->assertEquals(config('wax.shop.inventory.max_cart_quantity'), $product->effective_inventory);

        $order->place();

        $listener = new InventoryListener();
        $listener->handle(new OrderPlacedEvent($order->fresh()));

        $product->refresh();
        $this->assertEquals(10, $product->inventory);
        $this->assertEquals(config('wax.shop.inventory.max_cart_quantity'), $product->effective_inventory);
    }
}
