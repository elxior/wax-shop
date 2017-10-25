<?php

namespace Tests\Shop;

use App\Shop\Models\Order;
use App\User;
use Carbon\Carbon;
use Tests\Shop\Traits\SeedsProducts;
use Tests\WaxAppTestCase;

class CartApiTest extends WaxAppTestCase
{
    use SeedsProducts;

    public function setUp()
    {
        parent::setUp();
        $this->seedProducts();
    }

    public function testGetInitialCart()
    {
        $response = $this->json('GET', route('shop::api.cart.index'));
        $response->assertStatus(200);
        $response->assertJsonStructure(['items']);

        $responseJson = $response->decodeResponseJson();
        $this->assertEmpty($responseJson['items']);
    }

    public function testAddBasicProduct()
    {
        $product = $this->products['basic'];

        $response = $this->json('POST', route('shop::api.cart.store'), [
            'product_id' => $product->id,
            'quantity' => 1
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'items' => [
                    0 => [
                        'id' => $product->id,
                    ]
                ]
            ]);
    }

    public function testAddInvalidProduct()
    {
        $response = $this->json('POST', route('shop::api.cart.store'), [
            'product_id' => 7777,
            'quantity' => 1
        ]);
        $response->assertStatus(422);
    }

    public function testAddProductWithOptions()
    {
        $product = $this->products['withOptions'];

        $options = $product->options->mapWithKeys(function ($option) {
            return [$option->id => $option->values->random()->id];
        })->all();

        $response = $this->json('POST', route('shop::api.cart.store'), [
            'product_id' => $product->id,
            'quantity' => 1,
            'options' => $options,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'items' => [
                    0 => [
                        'product_id' => $product->id,
                    ]
                ]
            ]);

        $cartOptions = collect($response->decodeResponseJson()['items'][0]['options']);
        $cartOptions = $cartOptions->mapWithKeys(function ($option) {
            return [$option['option_id'] => $option['value_id']];
        })->all();

        $this->assertEquals($options, $cartOptions);
    }

    public function testAddProductWithMissingOptions()
    {
        $response = $this->json('POST', route('shop::api.cart.store'), [
            'product_id' => $this->products['withOptions']->id,
            'quantity' => 1,
        ]);

        $response->assertStatus(422);
    }

    public function testAddOnePerUserProductInvalidQuantity()
    {
        $response = $this->json('POST', route('shop::api.cart.store'), [
            'product_id' => $this->products['onePerUser']->id,
            'quantity' => 2
        ]);
        $response->assertStatus(422);
    }

    public function testInvalidQuantityWithTrackingEnabled()
    {
        config(['wax.shop.inventory.track' => true]);

        $product = $this->products['basic'];

        $response = $this->json('POST', route('shop::api.cart.store'), [
            'product_id' => $product->id,
            'quantity' => $product->effective_inventory + 1,
        ]);

        $response->assertStatus(422);
    }

    public function testInvalidQuantityWithTrackingDisabled()
    {
        config(['wax.shop.inventory.track' => false]);

        $product = $this->products['basic'];

        $response = $this->json('POST', route('shop::api.cart.store'), [
            'product_id' => $product->id,
            'quantity' => $product->effective_inventory + 1,
        ]);

        $response->assertStatus(422);
    }

    public function testAddOnePerUserWithOptionsProductAlreadyInCart()
    {
        $product = $this->products['onePerUserWithOptions'];

        $options = $product->options->mapWithKeys(function ($option) {
            return [$option->id => $option->values->random()->id];
        })->all();

        $user = factory(User::class)->create();

        $requestData = [
            'product_id' => $product->id,
            'quantity' => 1,
            'options' => $options,
        ];

        $response = $this->actingAs($user)
            ->json('POST', route('shop::api.cart.store'), $requestData);
        $response->assertStatus(200);

        $response = $this->actingAs($user)
            ->json('POST', route('shop::api.cart.store'), $requestData);
        $response->assertStatus(422);
    }

    public function testAddOnePerUserProductAlreadyOwned()
    {
        $product = $this->products['onePerUserWithOptions'];

        $options = $product->options->mapWithKeys(function ($option) {
            return [$option->id => $option->values->random()->id];
        })->all();

        $user = factory(User::class)->create();

        $requestData = [
            'product_id' => $product->id,
            'quantity' => 1,
            'options' => $options,
        ];

        $response = $this->actingAs($user)
            ->json('POST', route('shop::api.cart.store'), $requestData);
        $response->assertStatus(200);

        // The user completed the order.
        $order = Order::find(1);
        $order->placed_at = Carbon::now();
        $order->save();

        // Cart is now empty
        $response = $this->json('GET', route('shop::api.cart.index'));
        $response->assertStatus(200);
        $responseJson = $response->decodeResponseJson();
        $this->assertEmpty($responseJson['items']);

        // Adding the same product again should fail
        $response = $this->actingAs($user)
            ->json('POST', route('shop::api.cart.store'), $requestData);
        $response->assertStatus(422);
    }
}
