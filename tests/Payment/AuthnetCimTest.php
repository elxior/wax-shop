<?php

namespace Tests\Shop\Payment;

use Tests\Shop\Support\ShopBaseTestCase;

class AuthnetCimTest extends ShopBaseTestCase
{
    // Yes, I have failed to write tests here. I think a good strategy would be to mock the http response objects and
    // stub the response data like how the default OmniPay integration tests work.

    public function testNothing()
    {
        // phpunit will throw a warning if there isn't actually a test.
    }
}
