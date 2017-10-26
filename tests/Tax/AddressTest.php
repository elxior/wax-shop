<?php

namespace Tests\Shop\Tax;

use Exception;
use Faker\Factory;
use Tests\TestCase;
use Wax\Shop\Support\Tax\Address;

class AddressTest extends TestCase
{
    protected $faker;

    public function setUp()
    {
        parent::setUp();

        $this->faker = Factory::create();
    }

    public function testConstructor()
    {
        $line1 = $this->faker->company;
        $line2 = $this->faker->streetAddress;
        $line3 = $this->faker->secondaryAddress;
        $city = $this->faker->city;
        $state = $this->faker->stateAbbr;
        $zip = $this->faker->postcode;
        $country = 'US';

        $address = new Address($line1, $line2, $line3, $city, $state, $zip, $country);

        $this->assertEquals($line1, $address->getLine1());
        $this->assertEquals($line2, $address->getLine2());
        $this->assertEquals($line3, $address->getLine3());
        $this->assertEquals($city, $address->getCity());
        $this->assertEquals($state, $address->getRegion());
        $this->assertEquals($zip, $address->getPostalCode());
        $this->assertEquals($country, $address->getCountry());
    }

    public function testSetters()
    {
        $line1 = $this->faker->company;
        $line2 = $this->faker->streetAddress;
        $line3 = $this->faker->secondaryAddress;
        $city = $this->faker->city;
        $state = $this->faker->stateAbbr;
        $zip = $this->faker->postcode;
        $country = 'MX';

        $address = (new Address)
            ->setLine1($line1)
            ->setLine2($line2)
            ->setLine3($line3)
            ->setCity($city)
            ->setRegion($state)
            ->setPostalCode($zip)
            ->setCountry($country);

        $this->assertEquals($line1, $address->getLine1());
        $this->assertEquals($line2, $address->getLine2());
        $this->assertEquals($line3, $address->getLine3());
        $this->assertEquals($city, $address->getCity());
        $this->assertEquals($state, $address->getRegion());
        $this->assertEquals($zip, $address->getPostalCode());
        $this->assertEquals($country, $address->getCountry());
    }

    public function testCountryValidationError()
    {
        $address = new Address;

        $this->expectException(Exception::class);

        $address->setCountry('No Good!');
    }

    public function testCountryValidationPassBlank()
    {
        $address = new Address;
        $address->setCountry('');
        $this->assertEquals('', $address->getCountry());
    }
}
