<?php

namespace Tests\Shop\Tax;

use App\Shop\Drivers\Tax\AvalaraDriver;
use App\Shop\Exceptions\Tax\AddressException;
use App\Shop\Exceptions\Tax\ApiException;
use App\Shop\Models\Product;
use Avalara\AvaTaxClient;

class AvalaraDriverTest extends TaxDriverTestCase
{

    /**
     * Enable stubbing for the API calls instead of sending real requests
     *
     * @var bool
     */
    protected $stubApiRequests = true;

    /**
     * track subsequent calls to testMultiShipment in order to load the correct stub file
     *
     * @var int
     */
    protected static $multiShipmentIndex=0;

    public function setUp()
    {
        parent::setUp();

        // comment this out to stop the tests from running for now
        config(['wax.shop.tax.driver' => AvalaraDriver::class]);
    }

    public function tearDown()
    {
        \Mockery::close();
    }

    public function testAuthenticationError()
    {
        $this->app->bind(
            AvaTaxClient::class,
            function ($app) {
                return (new AvaTaxClient('Wax', '1.0', 'localhost', 'sandbox'))
                    ->withSecurity('bad', 'noauth');
            }
        );

        $this->setAvaTaxClientResponseStub('testAuthenticationError.json');

        $this->expectException(ApiException::class);

        parent::testGetTax();
    }

    public function testInvalidAddress()
    {
        $this->setAvaTaxClientResponseStub('testInvalidAddress.json');

        $this->expectException(AddressException::class);

        parent::testInvalidAddress();
    }

    public function testGetTax()
    {
        $this->setAvaTaxClientResponseStub('testGetTax.json');
        parent::testGetTax();
    }

    public function testCommit()
    {
        $this->setAvaTaxClientResponseStub('testCommitSuccess.json');
        parent::testCommit();
    }

    public function testCommitError()
    {
        $this->setAvaTaxClientResponseStub('testCommitError.json');

        $this->shopService->addOrderItem(
            factory(Product::class)->create(['price' => 10])->id
        );

        $this->shopService->setShippingAddress(
            $this->faker->firstName,
            $this->faker->lastName,
            $this->faker->company,
            $this->faker->safeEmail,
            $this->faker->phoneNumber,
            '101 Bork Street',
            $this->faker->secondaryAddress,
            'Borksburg',
            'BK',
            '87653-0909',
            'US'
        );

        $shipment = $this->shopService->getActiveOrder()->default_shipment;

        $result = $shipment->commitTax();

        $this->assertFalse($result);
    }

    public function testMultiItem()
    {
        $this->setAvaTaxClientResponseStub('testMultiItem.json');
        parent::testMultiItem();
    }

    public function testMultiShipment()
    {
        if ($this->stubApiRequests) {
            app()->bind(
                AvaTaxClient::class,
                function ($app) {
                    $mock = (\Mockery::mock(AvaTaxClient::class));
                    $index = ++static::$multiShipmentIndex;
                    $mock->shouldReceive('createTransaction')
                        ->andReturn(
                            $this->getStub("testMultiShipment{$index}.json")
                        );

                    return $mock;
                }
            );
        }

        parent::testMultiShipment();
    }

    public function testMixedTaxability()
    {
        $this->setAvaTaxClientResponseStub('testMixedTaxability.json');
        parent::testMixedTaxability();
    }

    public function testLocalTax()
    {
        $this->setAvaTaxClientResponseStub('testLocalTax.json');

        $this->shopService->addOrderItem(
            factory(Product::class)->create(['price' => 10])->id
        );

        // Denver is an example with state and local taxes
        $this->setShippingAddress([
            'city' => 'Denver',
            'state' => 'CO',
            'zip' => '80210',
        ]);

        $order = $this->shopService->getActiveOrder();
        $order->default_shipment->calculateTax();

        $this->assertEquals('CO 7.65%', $order->default_shipment->tax_desc);
        $this->assertEquals(7.65, $order->default_shipment->tax_rate);
        $this->assertEquals(.77, $order->default_shipment->tax_amount);
        $this->assertEquals(10.77, $order->default_shipment->gross_total);

        $order = $this->shopService->getActiveOrder();
        $this->assertEquals(.77, $order->tax_subtotal);
        $this->assertEquals(10.77, $order->gross_total);
    }

    protected function setAvaTaxClientResponseStub($stubName)
    {
        if (!$this->stubApiRequests) {
            return;
        }

        app()->bind(
            AvaTaxClient::class,
            function ($app) use ($stubName) {
                $mock = (\Mockery::mock(AvaTaxClient::class));
                $mock->shouldReceive('createTransaction')
                    ->andReturn(
                        $this->getStub($stubName)
                    );

                return $mock;
            }
        );
    }

    protected function getStub($stubName)
    {
        $path = __DIR__;
        $result = file_get_contents("{$path}/stubs/Avalara/{$stubName}");

        if (false === $result) {
            throw new \Exception("Could not load stub: {$stubName}");
        }

        return json_decode($result) ?? $result;
    }
}