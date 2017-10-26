<?php
return [
    'driver' => \Wax\Shop\Drivers\Tax\DbDriver::class,

    'avalara' => [
        'account_id' => env('AVALARA_ACCOUNT_ID', 'xxx'),
        'license_key' => env('AVALARA_LICENSE_KEY', 'xxx'),
        'company_code' => env('AVALARA_COMPANY_CODE', 'DEFAULT')
    ]
];
