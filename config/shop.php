<?php
/**
 * These configuration values are prefixed with 'wax.shop.', e.g. `config('wax.shop.models.product')`
 */
return [
    'ratings' => [
        'min' => 1,
        'max' => 5,
        'increment' => .5,
    ],
    'inventory' => [
        'track' => true,

        /**
         * Limits how many of a single item can be added to cart by putting a ceiling on the "effective inventory".
         */
        'max_cart_quantity' => 10000,
    ],
    'models' => [
        'product' => Wax\Shop\Models\Product::class,
        'payment_method' => Wax\Shop\Models\User\PaymentMethod::class,
    ],
    'payment' => [
        'drivers' => [
            'authorizenet_cim' => [
                'api_login_id' => env('AUTHORIZE_NET_API_LOGIN_ID'),
                'transaction_key' => env('AUTHORIZE_NET_TRANSACTION_KEY'),
                'test_mode' => env('AUTHORIZE_NET_TEST_MODE', false),
                'developer_mode' => env('AUTHORIZE_NET_DEVELOPER_MODE', true),
            ]
        ],
        'stored_payment_driver' => \Wax\Shop\Payment\Drivers\AuthorizeNetCimDriver::class,
    ]
];
