<?php

namespace Tests\Shop\Payment;

use Tests\Shop\Support\ShopBaseTestCase;
use Wax\Shop\Payment\Types\CreditCard;
use Wax\Shop\Payment\Types\StoredCreditCard;

class CreditCardExpiryDataTest extends ShopBaseTestCase
{
    /* @var ShopService $shop */
    protected $cc;
    protected $scc;

    public function setUp()
    {
        parent::setUp();

        $this->cc = new CreditCard;
        $this->scc = new StoredCreditCard;
    }

    /**
     * @dataProvider provideExpiryData
     */
    public function testCreditCardExpiryData($input, $output)
    {
        $data = [
            'number' => '4111111111111111',
            'expiry' => $input,
            'cvc' => '123',
            'name' => 'Test User',
            'billing-address' => '908 S. 8th St',
            'postal-code' => '40203',
        ];

        $cardData = $this->cc->getCardData($data);

        $this->assertEquals(
            [$cardData['expiryMonth'], $cardData['expiryYear']],
            $output
        );
    }

    /**
     * @dataProvider provideExpiryData
     */
    public function testStoredCreditCardExpiryData($input, $output)
    {
        $data = [
            'number' => '4111111111111111',
            'expiry' => $input,
            'cvc' => '123',
            'name' => 'Test User',
            'billing-address' => '908 S. 8th St',
            'postal-code' => '40203',
        ];

        $cardData = $this->scc->getCardData($data);

        $this->assertEquals(
            [$cardData['expiryMonth'], $cardData['expiryYear']],
            $output
        );
    }

    public function provideExpiryData()
    {
        return [
            [
                '1/21',
                ['01', '21'],
            ],
            [
                '01/21',
                ['01', '21'],
            ],
            [
                '1/2021',
                ['01', '21'],
            ],
            [
                '01/2021',
                ['01', '21'],
            ],
            [
                '1 / 21',
                ['01', '21'],
            ],
            [
                '01 / 21',
                ['01', '21'],
            ],
            [
                '1 / 2021',
                ['01', '21'],
            ],
            [
                '01 / 2021',
                ['01', '21'],
            ],
            [
                '0121',
                ['01', '21'],
            ],
        ];
    }
}
