<?php

use App\Localization\Currency;
use Carbon\Carbon;

$structure = [
    'table' => 'coupons',
    'primary_key' => 'id',
    'fields' => [],
    'notes' =>
                '<ul>'.
                '<li style="display: inline; list-style-type: none; padding-right: 20px;"><a href="/admin/coupon_generate.php">&bull; Bulk <strong>Generate</strong> Coupon Codes</a></li>'.
                '<li style="display: inline; list-style-type: none; padding-right: 20px;"><a href="/admin/coupon_import.php">&bull; Bulk <strong>Load</strong> Coupons From CSV</a></li>'.
                '<li style="display: inline; list-style-type: none; padding-right: 20px;"><a href="/admin/coupon_export.php">&bull; Bulk <strong>Export</strong> Coupons To CSV</a></li>'.
                '</ul>',
    'settings' => [
        'page_title' => 'Coupons',
        'create_new_text' => 'Create New Coupon',
        'back_link' => 'index.php',
        'back_link_text' => 'main menu',
        'editor_subtitle' => 'coupon information',
        'list_fields' => ['code', 'title', 'expired_at'],
        'order' => ['code'],
        'order_direction' => ['asc'],
        'search_fields' => ['code', 'title'],
    ],
    'presave' => function ($id) {
        DB::table('coupons')
            ->where('expired_at', '<', Carbon::today()->toDateString() . ' 23:59:59')
            ->where('id', '<>', $id)
            ->delete();

        if ($this->data->field('dollars') > 0 && $this->data->field('percent') > 0) {
            $this->setError('You may set only one of "Dollars Off" or "Percent Off"');
            return false;
        }

        $this->data->field('code', strtoupper($this->data->field('code')));

        return true;
    }
];

$structure['fields'][] = [
    'name' => 'title',
    'display_name' => 'Title',
    'type' => 'text',
];

$structure['fields'][] = [
    'group' => 'Discount',
    'name' => 'percent',
    'display_name' => 'Percent Off',
    'type' => 'integer',
    'display_callback' => function () {
        return "{$this->currentItem[$this->currentField['name']]}%";
    },
    'default' => '0',
    'validate' => ['integer', 0, 50]
];

$structure['fields'][] = [
    'group' => 'Discount',
    'name' => 'dollars',
    'display_name' => 'Dollars Off',
    'type' => 'float',
    'precision' => 2,
    'display_callback' => function () {
        return Currency::format($this->currentItem[$this->currentField['name']]);
    },
    'default' => '0',
    'validate' => ['float', 0, false]
];

$structure['fields'][] = [
    'group' => 'Discount',
    'name' => 'minimum_order',
    'display_name' => 'Minimum Order',
    'type' => 'float',
    'precision' => 2,
    'display_callback' => function () {
        return Currency::format($this->currentItem[$this->currentField['name']]);
    },
    'default' => '0',
    'validate' => ['float', 0, false]

];

$structure['fields'][] = [
    'group' => 'Coupon Code',
    'name' => 'code',
    'display_name' => 'Code',
    'type' => 'text',
    'validate' => ['string', 1, 32],
    'unique' => true
];

$structure['fields'][] = [
    'group' => 'Coupon Code',
    'name' => 'expired_at',
    'display_name' => 'Expiration Date',
    'type' => 'sqltimestamp',
    'validate' => true,
    'notes' => 'Coupon is valid through 11:59 pm on the expiration date.',
    'nullable' => true,
];

$structure['fields'][] = [
    'group' => 'Coupon Code',
    'name' => 'one_time',
    'display_name' => 'One-Time Use',
    'type' => 'boolean',
    'default' => '0'
];

$structure['fields'][] = [
    'group' => 'Coupon Code',
    'name' => 'include_shipping',
    'display_name' => 'Include Shipping',
    'type' => 'boolean',
    'default' => '0'
];
