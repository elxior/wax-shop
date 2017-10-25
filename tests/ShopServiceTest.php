<?php

namespace Tests\Shop;

use App\Shop\Exceptions\ValidationException;
use App\Shop\Facades\ShopServiceFacade;
use App\Shop\Models\Order;
use App\Shop\Services\ShopService;
use App\User;
use Carbon\Carbon;
use Faker\Factory;
use Illuminate\Support\Facades\Auth;
use Tests\Shop\Traits\SeedsProducts;
use Tests\WaxAppTestCase;

class ShopServiceTest extends WaxAppTestCase
{
    use SeedsProducts;

    /* @var \App\Shop\Services\ShopService */
    protected $shopService;

    /* @var \Faker\Generator */
    protected $faker;

    public function setUp()
    {
        parent::setUp();
        $this->seedProducts();

        $this->shopService = app()->make(ShopService::class);
        $this->faker = Factory::create();
    }

    public function testGetActiveOrder()
    {
        $order = $this->shopService->getActiveOrder();
        $this->assertInstanceOf(Order::class, $order);

        $this->assertEmpty($order->default_shipment->items->all());
    }

    public function testAddProductWithOptions()
    {
        $product = $this->products['withOptions'];

        $options = $product->options->mapWithKeys(function ($option) {
            return [$option->id => $option->values->random()->id];
        })->all();

        $this->shopService->addOrderItem($product->id, 1, $options);

        $order = $this->shopService->getActiveOrder();
        $this->assertNotEmpty($order->default_shipment->items->all());
    }

    /**
     * Test that added an item twice results in a single cart item with a sum of quantities
     */
    public function testDuplicateCartItems()
    {
        $product = $this->products['withOptions'];

        $options = $product->options->mapWithKeys(function ($option) {
            return [$option->id => $option->values->random()->id];
        })->all();

        $this->shopService->addOrderItem($product->id, 1, $options);
        $this->shopService->addOrderItem($product->id, 2, $options);

        $order = $this->shopService->getActiveOrder();
        $this->assertEquals($order->default_shipment->items->count(), 1);
        $this->assertEquals($order->default_shipment->items->first()->quantity, 3);
    }

    public function testDeleteCartItems()
    {
        // add a basic item
        $this->shopService->addOrderItem($this->products['basic']->id, 1, []);

        // add a product with options
        $product = $this->products['withOptions'];
        $options = $product->options->mapWithKeys(function ($option) {
            return [$option->id => $option->values->random()->id];
        })->all();
        $this->shopService->addOrderItem($product->id, 1, $options);

        $order = $this->shopService->getActiveOrder();
        $this->assertEquals($order->default_shipment->items->count(), 2);

        $this->shopService->deleteOrderItem($order->default_shipment->items->last()->id);
        $this->assertEquals($order->default_shipment->items->count(), 1);

        $this->shopService->deleteOrderItem($order->default_shipment->items->first()->id);
        $this->assertEquals($order->default_shipment->items->count(), 0);
    }

    public function testUpdateCartItemQuantity()
    {
        // add a basic item
        $this->shopService->addOrderItem($this->products['basic']->id, 1, []);

        $order = $this->shopService->getActiveOrder();
        $this->assertEquals($order->default_shipment->items->first()->quantity, 1);

        $this->shopService->updateOrderItemQuantity($order->default_shipment->items->first()->id, 2);

        $order = $order->fresh();
        $this->assertEquals($order->default_shipment->items->first()->quantity, 2);
        $this->assertEquals($this->products['basic']->price * 2, $order->item_gross_subtotal);
    }

    public function testDeleteInvalidCartItem()
    {
        $this->assertEquals($this->shopService->deleteOrderItem(777), false);
    }

    public function testCartHasItem()
    {
        // add a basic item
        $this->shopService->addOrderItem($this->products['basic']->id, 1, []);

        // add a product with options
        $product = $this->products['withOptions'];
        $options = $product->options->mapWithKeys(function ($option) {
            return [$option->id => $option->values->random()->id];
        })->all();
        $this->shopService->addOrderItem($product->id, 1, $options);

        // test that the basic product is in the cart
        $this->assertEquals($this->shopService->orderHasProduct($this->products['basic']->id, [], []), true);

        // test productWithOptions matches when the same options are given, but fails when options are empty
        $this->assertEquals($this->shopService->orderHasProduct($product->id, $options, []), true);
        $this->assertEquals($this->shopService->orderHasProduct($product->id, [], []), false);

        // test that an item that wasn't added to the cart returns false
        $this->assertEquals($this->shopService->orderHasProduct($this->products['onePerUser']->id, [], []), false);
    }

    public function testOrderHasItem()
    {
        // set up an order with two shipments
        $order = $this->shopService->getActiveOrder();
        $order->shipments()->create([]);
        $order->shipments()->create([]);
        $order->refresh();

        // add a basic item
        $order->shipments[0]->addItem($this->products['basic']->id, 1, []);

        // add a product with options
        $productWithOptions = $this->products['withOptions'];
        $options = $productWithOptions->options->mapWithKeys(function ($option) {
            return [$option->id => $option->values->random()->id];
        })->all();
        $order->shipments[1]->addItem($productWithOptions->id, 1, $options);

        // test that the basic product is in the cart
        $this->assertTrue($this->shopService->orderHasProduct($this->products['basic']->id, []));

        // test that the second shipment's item doesn't come back in the default shipment check
        $this->assertNull(
            $this->shopService->getActiveOrder()->default_shipment->findItem($productWithOptions->id, $options)
        );

        // test productWithOptions matches when the same options are given, but fails when options are empty
        $this->assertTrue($this->shopService->orderHasProduct($productWithOptions->id, $options));
        $this->assertfalse($this->shopService->orderHasProduct($productWithOptions->id, []));

        // test that an item that wasn't added to the cart returns false
        $this->assertFalse($this->shopService->orderHasProduct($this->products['onePerUser']->id, []));
    }


    public function testAddOnePerUserWithOptionsProductAlreadyInCart()
    {
        $product = $this->products['onePerUserWithOptions'];

        $options = $product->options->mapWithKeys(function ($option) {
            return [$option->id => $option->values->random()->id];
        })->all();

        // should work once
        $this->shopService->addOrderItem($product->id, 1, $options);

        // ... and fail the second time
        $this->expectException(ValidationException::class);
        $this->shopService->addOrderItem($product->id, 1, $options);

        $this->assertEquals(1, $this->shopService->getActiveOrder()->total_quantity);
    }

    public function testAddOnePerUserProductAlreadyOwned()
    {
        $product = $this->products['onePerUserWithOptions'];

        $options = $product->options->mapWithKeys(function ($option) {
            return [$option->id => $option->values->random()->id];
        })->all();

        // Simulate logged-in user
        $user = factory(User::class)->create();
        Auth::login($user);

        // simulate completed order
        $this->shopService->addOrderItem($product->id, 1, $options);
        $order = $this->shopService->getActiveOrder();
        $order->placed_at = Carbon::now();
        $order->save();

        // start a new order and add the item to the cart... should fail
        $this->expectException(ValidationException::class);
        $this->shopService->addOrderItem($product->id, 1, $options);

        $this->assertEquals(2, $this->shopService->getActiveOrder()->id);
        $this->assertEquals(0, $this->shopService->getActiveOrder()->total_quantity);
    }

    public function testSetShippingAddress()
    {
        $firstname = $this->faker->firstName;
        $lastname = $this->faker->lastName;
        $company = $this->faker->company;
        $email = $this->faker->safeEmail;
        $phone = $this->faker->phoneNumber;
        $address1 = $this->faker->streetAddress;
        $address2 = $this->faker->secondaryAddress;
        $city = $this->faker->city;
        $state = $this->faker->stateAbbr;
        $zip = $this->faker->postcode;
        $country = 'US';

        $this->shopService->setShippingAddress(
            $firstname,
            $lastname,
            $company,
            $email,
            $phone,
            $address1,
            $address2,
            $city,
            $state,
            $zip,
            $country
        );

        $shipment = $this->shopService->getActiveOrder()->default_shipment;
        $this->assertEquals($firstname, $shipment->firstname);
        $this->assertEquals($lastname, $shipment->lastname);
        $this->assertEquals($company, $shipment->company);
        $this->assertEquals($email, $shipment->email);
        $this->assertEquals($phone, $shipment->phone);
        $this->assertEquals($address1, $shipment->address1);
        $this->assertEquals($address2, $shipment->address2);
        $this->assertEquals($city, $shipment->city);
        $this->assertEquals($state, $shipment->state);
        $this->assertEquals($country, $shipment->country);
    }


    public function testInventoryConsidersMultipleShipmentsBasic()
    {
        config(['wax.shop.inventory.track' => true]);

        // set up an order with two shipments
        $order = $this->shopService->getActiveOrder();
        $order->shipments()->create([]);
        $order->shipments()->create([]);
        $order->refresh();

        $product = $this->products['basic'];
        $product->inventory = 1;
        $product->save();

        // add a basic item
        $order->shipments[0]->addItem($product->id, 1);

        $this->expectException(ValidationException::class);
        $order->shipments[1]->addItem($product->id, 1);
    }

    public function testInventoryConsidersMultipleShipmentsWithOptionModifiers()
    {
        config(['wax.shop.inventory.track' => true]);

        // set up an order with two shipments
        $order = $this->shopService->getActiveOrder();
        $order->shipments()->create([]);
        $order->shipments()->create([]);
        $order->refresh();

        $product = $this->products['withOptionModifiers'];
        // the default seeded optionModifiers don't have an inventory set so they will inherit the product inventory.
        $product->inventory = 1;
        $product->save();

        $options = $product->options->mapWithKeys(function ($option) {
            return [$option->id => $option->values->random()->id];
        })->all();

        // add a basic item
        $order->shipments[0]->addItem($product->id, 1, $options);

        $this->expectException(ValidationException::class);
        $order->shipments[1]->addItem($product->id, 1, $options);
    }

    public function testUpdateOrderItemQuantityConsidersMultipleShipmentsBasic()
    {
        config(['wax.shop.inventory.track' => true]);

        // set up an order with two shipments
        $order = $this->shopService->getActiveOrder();
        $order->shipments()->create([]);
        $order->shipments()->create([]);
        $order->refresh();

        $product = $this->products['basic'];
        $product->inventory = 2;
        $product->save();

        // add a basic item
        $order->shipments[0]->addItem($product->id, 1);
        $order->shipments[1]->addItem($product->id, 1);

        $this->expectException(ValidationException::class);
        $this->shopService->updateOrderItemQuantity(1, 2);
    }

    public function testUpdateOrderItemQuantityConsidersMultipleShipmentsWithOptions()
    {
        config(['wax.shop.inventory.track' => true]);

        // set up an order with two shipments
        $order = $this->shopService->getActiveOrder();
        $order->shipments()->create([]);
        $order->shipments()->create([]);
        $order->refresh();

        $product = $this->products['withOptions'];
        $product->inventory = 2;
        $product->save();

        $options = $product->options->mapWithKeys(function ($option) {
            return [$option->id => $option->values->random()->id];
        })->all();

        // add a basic item
        $order->shipments[0]->addItem($product->id, 1, $options);
        $order->shipments[1]->addItem($product->id, 1, $options);

        $this->expectException(ValidationException::class);
        $this->shopService->updateOrderItemQuantity(1, 2, $options);
    }
}
