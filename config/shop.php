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
    ]
];
