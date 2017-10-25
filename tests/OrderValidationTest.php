<?php

namespace Tests\Shop;

use App\Shop\Models\Order\ShippingRate;
use App\Shop\Models\Product;
use App\Shop\Drivers\Tax\DbDriver;
use App\Shop\Services\ShopService;
use Tests\Shop\Traits\SetsShippingAddress;
use Tests\WaxAppTestCase;

class OrderValidationTest extends WaxAppTestCase
{
    use SetsShippingAddress;

    /* @var ShopService */
    protected $shopService;

    public function setUp()
    {
        parent::setUp();

        $this->shopService = app()->make(ShopService::class);

        config(['wax.shop.tax_driver' => DbDriver::class]);
    }

    public function testValidateTax()
    {
        $this->assertFalse($this->shopService->getActiveOrder()->validateTax());

        $this->setShippingAddress();
        $this->shopService->calculateTax();

        $this->assertTrue($this->shopService->getActiveOrder()->validateTax());
    }

    public function testValidateHasItems()
    {
        $this->assertFalse($this->shopService->getActiveOrder()->validateHasItems());

        $this->shopService->addOrderItem(
            factory(Product::class)->create(['price' => 10])->id
        );

        $this->assertTrue($this->shopService->getActiveOrder()->validateHasItems());
    }

    public function testValidateShippingRequired()
    {
        $this->assertFalse($this->shopService->getActiveOrder()->validateShipping());

        $this->shopService->addOrderItem(
            factory(Product::class)->create(['shipping_enable_rate_lookup' => true])->id
        );
        $this->assertFalse($this->shopService->getActiveOrder()->validateShipping());

        $this->shopService->setShippingService(factory(ShippingRate::class)->create());
        $this->assertTrue($this->shopService->getActiveOrder()->validateShipping());
    }

    public function testValidateShippingNotRequired()
    {
        $this->assertFalse($this->shopService->getActiveOrder()->validateShipping());

        $this->shopService->addOrderItem(
            factory(Product::class)->create(['shipping_enable_rate_lookup' => false])->id
        );
        $this->assertTrue($this->shopService->getActiveOrder()->validateShipping());
    }

    public function testValidateInventory()
    {
        $product = factory(Product::class)->create(['inventory' => 1]);
        $this->shopService->addOrderItem($product->id);

        $this->assertTrue($this->shopService->getActiveOrder()->validateInventory());

        $product->inventory = 0;
        $product->save();
        $this->assertFalse($this->shopService->getActiveOrder()->validateInventory());
    }
}
