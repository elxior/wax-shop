<?php

namespace Tests\Shop\Traits;

use Faker\Factory;
use Wax\Shop\Facades\ShopServiceFacade;
use Wax\Shop\Models\Order\Shipment;

trait SetsShippingAddress
{
    protected function setKyShippingAddress()
    {
        $this->setShippingAddress([
            'city' => 'Louisville',
            'state' => 'KY',
            'zip' => '40203',
            'country' => 'US'
        ]);
    }

    protected function setShippingAddress($values = [])
    {
        if (!isset($this->faker)) {
            $this->faker = Factory::create();
        }

        ShopServiceFacade::setShippingAddress(
            $values['firstname'] ?? $this->faker->firstName,
            $values['lastname'] ?? $this->faker->lastName,
            $values['company'] ?? $this->faker->company,
            $values['email'] ?? $this->faker->safeEmail,
            $values['phone'] ?? $this->faker->phoneNumber,
            $values['address1'] ?? $this->faker->streetAddress,
            $values['address2'] ?? $this->faker->secondaryAddress,
            $values['city'] ?? $this->faker->city,
            $values['state'] ?? $this->faker->stateAbbr,
            $values['zip'] ?? $this->faker->postcode,
            $values['country'] ?? 'US'
        );
    }

    protected function setShipmentShippingAddress(Shipment $shipment, $values = [])
    {
        if (!isset($this->faker)) {
            $this->faker = Factory::create();
        }

        $shipment->setAddress(
            $values['firstname'] ?? $this->faker->firstName,
            $values['lastname'] ?? $this->faker->lastName,
            $values['company'] ?? $this->faker->company,
            $values['email'] ?? $this->faker->safeEmail,
            $values['phone'] ?? $this->faker->phoneNumber,
            $values['address1'] ?? $this->faker->streetAddress,
            $values['address2'] ?? $this->faker->secondaryAddress,
            $values['city'] ?? $this->faker->city,
            $values['state'] ?? $this->faker->stateAbbr,
            $values['zip'] ?? $this->faker->postcode,
            $values['country'] ?? 'US'
        );
    }

}
