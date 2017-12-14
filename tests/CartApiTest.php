<?php

namespace Tests\Shop;

use App\User;
use Carbon\Carbon;
use Tests\Shop\Traits\SeedsProducts;
use Tests\Shop\Support\ShopBaseTestCase;
use Wax\Shop\Models\Order;

class CartApiTest extends ShopBaseTestCase
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

    public function testUpdateItemQuantity()
    {
        $product = $this->products['basic'];

        $user = factory(User::class)->create();

        $response = $this->actingAs($user)
            ->json('POST', route('shop::api.cart.store'), [
                'product_id' => $product->id,
                'quantity' => 1
            ]);

        $response->assertStatus(200);
        $responseData = $response->decodeResponseJson();
        $this->assertEquals($product->id, $responseData['items'][0]['product_id']);
        $this->assertEquals(1, $responseData['items'][0]['quantity']);
        $this->assertGreaterThan(0, $responseData['items'][0]['id']);

        $response = $this->actingAs($user)
            ->json(
                'PATCH',
                route('shop::api.cart.update', ['id' => $responseData['items'][0]['id']]),
                ['quantity' => 2]
            );

        $responseData = $response->decodeResponseJson();
        $response->assertStatus(200);
        $responseData = $response->decodeResponseJson();
        $this->assertEquals($product->id, $responseData['items'][0]['product_id']);
        $this->assertEquals(2, $responseData['items'][0]['quantity']);
    }

    public function testUpdateItemQuantityInvalidId()
    {
        $response = $this->json(
            'PATCH',
            route('shop::api.cart.update', ['id' => 777]),
            ['quantity' => 2]
        );
        $response->assertStatus(422);
    }

    public function testDeleteItem()
    {
        $product = $this->products['basic'];

        $user = factory(User::class)->create();

        $response = $this->actingAs($user)
            ->json('POST', route('shop::api.cart.store'), [
                'product_id' => $product->id,
                'quantity' => 1
            ]);

        $response->assertStatus(200);
        $responseData = $response->decodeResponseJson();
        $this->assertEquals($product->id, $responseData['items'][0]['product_id']);
        $this->assertEquals(1, $responseData['items'][0]['quantity']);
        $this->assertGreaterThan(0, $responseData['items'][0]['id']);

        $response = $this->actingAs($user)
            ->json(
                'DELETE',
                route('shop::api.cart.destroy', ['id' => $responseData['items'][0]['id']])
            );

        $response->assertStatus(200);
        $responseJson = $response->decodeResponseJson();
        $this->assertEmpty($responseJson['items']);
    }

    public function testDeleteItemInvalidId()
    {
        $response = $this->json(
            'DELETE',
            route('shop::api.cart.destroy', ['id' => 777])
        );
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
