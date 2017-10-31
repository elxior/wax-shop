<?php

namespace Tests\Shop;

use App\User;
use Tests\Shop\Traits\SeedsProducts;
use Tests\Shop\ShopBaseTestCase;
use Wax\Shop\Models\Coupon;
use Wax\Shop\Services\ShopService;

class CouponApiTest extends ShopBaseTestCase
{
    use SeedsProducts;

    /* @var ShopService $shop */
    protected $shopService;

    public function setUp()
    {
        parent::setUp();

        $this->seedProducts();
        $this->shopService = app()->make(ShopService::class);
    }

    public function testApply()
    {
        $coupon = factory(Coupon::class)
            ->create([
                'dollars' => 1,
            ]);

        $user = factory(User::class)->create();

        $product = $this->products['basic'];

        $product->price = 10;
        $product->save();

        $response = $this->actingAs($user)
            ->json('POST', route('shop::api.cart.store'), [
                'product_id' => $product->id,
                'quantity' => 1
            ]);
        $response->assertStatus(200);

        $response = $this->actingAs($user)
            ->json('POST', route('shop::api.coupon.store'), ['code' => $coupon->code]);
        $response->assertStatus(200)
            ->assertJson(['gross_total' => 10])
            ->assertJson(['coupon_value' => 1])
            ->assertJson(['total' => 9]);
    }

    public function testRemove()
    {
        $coupon = factory(Coupon::class)
            ->create([
                'dollars' => 1,
            ]);

        $user = factory(User::class)->create();

        $product = $this->products['basic'];

        $product->price = 10;
        $product->save();

        $response = $this->actingAs($user)
            ->json('POST', route('shop::api.cart.store'), [
                'product_id' => $product->id,
                'quantity' => 1
            ]);
        $response->assertStatus(200);

        $response = $this->actingAs($user)
            ->json('POST', route('shop::api.coupon.store'), ['code' => $coupon->code]);
        $response->assertStatus(200)
            ->assertJson(['gross_total' => 10])
            ->assertJson(['coupon_value' => 1])
            ->assertJson(['total' => 9]);

        $response = $this->actingAs($user)
            ->json('DELETE', route('shop::api.coupon.store'));
        $response->assertStatus(200)
            ->assertJson(['coupon' => null])
            ->assertJson(['gross_total' => 10])
            ->assertJson(['coupon_value' => 0])
            ->assertJson(['total' => 10]);
    }
}

