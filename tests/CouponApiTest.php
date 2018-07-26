<?php

namespace Tests\Shop;

use App\User;
use Illuminate\Support\Facades\Lang;
use Tests\Shop\Traits\SeedsProducts;
use Tests\Shop\Support\ShopBaseTestCase;
use Wax\Shop\Models\Coupon;
use Wax\Shop\Services\ShopService;

class CouponApiTest extends ShopBaseTestCase
{
    use SeedsProducts;

    /* @var ShopService $shop */
    protected $shopService;

    protected $invalidCouponResponse = 'Invalid coupon response string';

    public function setUp()
    {
        parent::setUp();

        $this->seedProducts();
        $this->shopService = app()->make(ShopService::class);

        Lang::shouldReceive('trans')
            ->andReturn('');

        Lang::shouldReceive('setLocale')
            ->andReturn('');

        Lang::shouldReceive('getFromJson')
            ->with('shop::coupon.invalid_code', [], null)
            ->andReturn($this->invalidCouponResponse);
    }

    public function testInvalidCouponResponse()
    {
        $response = $this->json('POST', route('shop::api.coupon.store'), ['code' => 'not-a-code']);
        $response->assertStatus(422)
            ->assertJson(['code' => [$this->invalidCouponResponse]]);
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

