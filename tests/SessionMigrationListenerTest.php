<?php

namespace Tests\Shop;

use App\User;
use Faker\Factory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Tests\Shop\Support\ShopBaseTestCase;
use Tests\Shop\Traits\BuildsPlaceableOrders;
use Wax\Core\Eloquent\Models\User\Address;
use Wax\Shop\Models\Order;
use Wax\Shop\Models\Product;
use Wax\Shop\Services\ShopService;

class SessionMigrationListenerTest extends ShopBaseTestCase
{
    use BuildsPlaceableOrders;

    /* @var ShopService */
    protected $shopService;

    /* @var \Faker\Generator */
    protected $faker;

    public function setUp()
    {
        parent::setUp();

        // testing orders produces emails
        Mail::fake();

        $this->shopService = app()->make(ShopService::class);
        $this->faker = Factory::create();
    }

    public function testBasicOrderMigration()
    {
        $originalSessionId = $this->shopService->getActiveOrder()->session_id;
        $this->assertNotEmpty($originalSessionId);

        session()->regenerate();

        $newSessionId = $this->shopService->getActiveOrder()->session_id;
        $this->assertNotEmpty($newSessionId);

        $this->assertNotEquals($originalSessionId, $newSessionId);
    }

    public function testBasicMigrationWithProduct()
    {
        $this->shopService->addOrderItem(factory(Product::class)->create()->id);
        $this->assertEquals(1, $this->shopService->getActiveOrder()->item_count);
        $this->assertEquals(1, Order::count());

        session()->regenerate();

        $this->assertEquals(1, $this->shopService->getActiveOrder()->item_count);
        $this->assertEquals(1, Order::count());
    }

    public function testAuthenticateUpdatesActiveOrder()
    {
        $this->assertNotEmpty($this->shopService->getActiveOrder()->session_id);
        $this->assertEmpty($this->shopService->getActiveOrder()->user_id);

        $user = factory(User::class)->create();
        Auth::login($user);

        $this->assertEmpty($this->shopService->getActiveOrder()->session_id);
        $this->assertEquals($user->id, $this->shopService->getActiveOrder()->user_id);

        $this->assertEquals(1, Order::count());
    }

    public function testAuthenticateUpdatesMultiplePlacedOrders()
    {
        $order1 = $this->buildPlaceableOrder();
        $this->assertTrue($order1->place());

        $order2 = $this->buildPlaceableOrder();
        $this->assertTrue($order2->place());

        $order1->refresh();
        $order2->refresh();
        $this->assertNotNull($order1->session_id);
        $this->assertNotNull($order2->session_id);

        $user = factory(User::class)->create();
        Auth::login($user);

        $order1->refresh();
        $order2->refresh();
        $this->assertNull($order1->session_id);
        $this->assertNull($order2->session_id);
        $this->assertEquals($user->id, $order1->user_id);
        $this->assertEquals($user->id, $order2->user_id);
    }

    public function testAuthenticateWithPlacedAndActiveOrders()
    {
        $placedOrder = $this->buildPlaceableOrder();
        $this->assertTrue($placedOrder->place());

        $activeOrder = $this->shopService->getActiveOrder();

        $user = factory(User::class)->create();
        Auth::login($user);

        $placedOrder->refresh();
        $activeOrder->refresh();

        $this->assertNull($placedOrder->session_id);
        $this->assertNull($activeOrder->session_id);
        $this->assertEquals($user->id, $placedOrder->user_id);
        $this->assertEquals($user->id, $activeOrder->user_id);

        $this->assertNotNull($placedOrder->placed_at);
        $this->assertNull($activeOrder->placed_at);

        // is() fails because getConnectionName is null until the model is retrieved, which fresh() fixes.
        $activeOrder = $activeOrder->fresh();
        $this->assertTrue($activeOrder->is($this->shopService->getActiveOrder()));
    }

    public function testLogout()
    {
        $user = factory(User::class)->create();
        Auth::login($user);

        $this->shopService->addOrderItem(factory(Product::class)->create()->id);
        $this->assertEquals(1, $this->shopService->getActiveOrder()->item_count);

        Auth::logout();

        $this->assertEquals(0, $this->shopService->getActiveOrder()->item_count);
        $this->assertEquals(2, Order::count());
    }

    public function testAuthenticateRestoresSavedCart()
    {
        $user = factory(User::class)->create();
        Auth::login($user);

        $this->shopService->addOrderItem(factory(Product::class)->create()->id);
        $this->assertEquals(1, $this->shopService->getActiveOrder()->item_count);

        Auth::logout();

        $this->assertEquals(0, $this->shopService->getActiveOrder()->item_count);
        $this->assertEquals(2, Order::count());

        Auth::login($user);

        $this->assertEquals(1, Order::count());
        $this->assertEquals(1, $this->shopService->getActiveOrder()->item_count);
        $this->assertEquals(1, Order::count());
    }

    public function testAuthenticatePreservesSessionCart()
    {
        $product1 = factory(Product::class)->create();
        $product2 = factory(Product::class)->create();

        $user = factory(User::class)->create();

        // Authenticated user adds an item to the cart but abandons it
        Auth::login($user);
        $this->shopService->addOrderItem($product1->id);
        $this->assertEquals(1, $this->shopService->getActiveOrder()->item_count);
        Auth::logout();

        // The visitor comes back and adds an item to the cart as a guest
        $this->shopService->addOrderItem($product2->id);
        $this->assertEquals(1, $this->shopService->getActiveOrder()->item_count);

        // user logs in- their current session cart should be maintained and the abandoned cart is deleted.
        Auth::login($user);

        $this->assertEquals(1, Order::count());
        $this->assertEquals(1, $this->shopService->getActiveOrder()->item_count);
        $this->assertEquals($product2->id, $this->shopService->getActiveOrder()->items->first()->product_id);
    }

    public function testAuthenticateDeletesIncompleteOrderWithEmptyCart()
    {
        $user = factory(User::class)->create();

        Auth::login($user);

        // start an order but don't add to cart
        $this->assertEquals(0, $this->shopService->getActiveOrder()->item_count);
        $this->assertEquals(1, Order::count());

        // leave / abandon the empty incomplete order
        Auth::logout();

        // return to the site, incomplete order should be gone
        Auth::login($user);

        $this->assertEquals(0, Order::count());
    }

    public function testAuthenticationUpdatesShipmentAddresses()
    {
        $user = factory(User::class)->create();
        $address = factory(Address::class)->create([
            'default_shipping' => true,
        ]);
        $user->addresses()->save($address);
        $user->refresh();

        $this->shopService->addOrderItem(factory(Product::class)->create()->id);

        Auth::login($user);

        $shipment = $this->shopService->getActiveOrder()->default_shipment;

        $this->assertEquals($address->firstname, $shipment->firstname);
        $this->assertEquals($address->lastname, $shipment->lastname);
        $this->assertEquals($address->email, $shipment->email);
        $this->assertEquals($address->phone, $shipment->phone);
        $this->assertEquals($address->company, $shipment->company);
        $this->assertEquals($address->address1, $shipment->address1);
        $this->assertEquals($address->address2, $shipment->address2);
        $this->assertEquals($address->city, $shipment->city);
        $this->assertEquals($address->state, $shipment->state);
        $this->assertEquals($address->zip, $shipment->zip);
        $this->assertEquals($address->country, $shipment->country);
    }

    public function testAuthenticationPreservesExistingShipmentAddress()
    {
        // User account with a default shipping address
        $user = factory(User::class)->create();
        $address = factory(Address::class)->create([
            'default_shipping' => true,
        ]);
        $user->addresses()->save($address);
        $user->refresh();

        // Guest adds an item to cart and sets the shipping address
        $this->shopService->addOrderItem(factory(Product::class)->create()->id);

        $shipment = $this->shopService->getActiveOrder()->default_shipment;
        $manualAddress = $this->randomAddressArray();
        $shipment->update($manualAddress);

        // After login, the shipment should still have the manually entered address, not their account default address.
        Auth::login($user);

        $shipment = $this->shopService->getActiveOrder()->default_shipment;

        $this->assertEquals($manualAddress['firstname'], $shipment->firstname);
        $this->assertEquals($manualAddress['lastname'], $shipment->lastname);
        $this->assertEquals($manualAddress['email'], $shipment->email);
        $this->assertEquals($manualAddress['phone'], $shipment->phone);
        $this->assertEquals($manualAddress['company'], $shipment->company);
        $this->assertEquals($manualAddress['address1'], $shipment->address1);
        $this->assertEquals($manualAddress['address2'], $shipment->address2);
        $this->assertEquals($manualAddress['city'], $shipment->city);
        $this->assertEquals($manualAddress['state'], $shipment->state);
        $this->assertEquals($manualAddress['zip'], $shipment->zip);
        $this->assertEquals($manualAddress['country'], $shipment->country);
    }

    protected function randomAddressArray($values = null)
    {
        return [
            'firstname' => $values['firstname'] ?? $this->faker->firstName,
            'lastname' => $values['lastname'] ?? $this->faker->lastName,
            'email' => $values['email'] ?? $this->faker->safeEmail,
            'phone' => $values['phone'] ?? $this->faker->phoneNumber,
            'company' => $values['company'] ?? $this->faker->company,
            'address1' => $values['address1'] ?? $this->faker->streetAddress,
            'address2' => $values['address2'] ?? $this->faker->secondaryAddress,
            'city' => $values['city'] ?? $this->faker->city,
            'state' => $values['state'] ?? $this->faker->stateAbbr,
            'zip' => $values['zip'] ?? $this->faker->postcode,
            'country' => $values['country'] ?? $this->faker->countryCode,
        ];
    }
}
