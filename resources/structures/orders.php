<?php

use Wax\Core\Structures\Builder;
use Wax\Core\Support\Localization\Currency;

$structure = [
    'table' => 'orders',
    'primary_key' => 'id',
    'fields' => [],
    'settings' => [
        'page_title' => 'Orders',
        'privilege' => 'Shop - Orders',
        'create_new_text' => 'Add Order',
        'allow_add' => false,
        'back_link' => 'index.php',
        'back_link_text' => 'main menu',
        'edit_route' => 'shop::orderDetails',
        'editor_subtitle' => 'Order Processing',
        'list_fields' => ['sequence', 'email', 'placed_at', 'total'],
        'search_fields' => ['user_id'],
        'order' => ['sequence'],
        'order_direction' => ['desc'],
        'sql_params' => 'placed_at IS NOT NULL',
    ]
];

$structure['filters'] = [
    '' => [ // this filter is selected by default since the key name is blank
        'label' => 'Show All',
        'use_structure_params' => true,
        'structure_params_operator' => 'and',
        'params' => '1=1'
    ],
    'pending' => [
        'label' => 'Awaiting Shipment',
        'use_structure_params' => true,
        'structure_params_operator' => 'and',
        'params' => 'shipped_at IS NULL'
    ],
    'approved' => [
        'label' => 'Shipped',
        'use_structure_params' => true,
        'structure_params_operator' => 'and',
        'params' => 'shipped_at IS NOT NULL'
    ],
];

$structure['fields'][] = [
    'name' => 'sequence',
    'display_name' => 'Sequence #',
    'type' => 'text',
    'readonly' => true,
];
$structure['fields'][] = [
    'name' => 'user_id',
    'display_name' => 'User',
    'type' => 'hidden',
    'bind' => CMS_BIND_ONE_TO_MANY,
    'bind_table' => 'users',
    'bind_key' => 'id',
    'bind_value' => [' ', 'firstname', 'lastname'],
    'bind_value_type' => 'text',
];

$structure['fields'][] = [
    'name' => 'email',
    'display_name' => 'Email',
    'type' => 'text',
];

$structure['fields'][] = [
    'name' => 'placed_at',
    'display_name' => 'Placed At',
    'type' => 'text',
    'readonly' => true,
];

$structure['fields'][] = [
    'name' => 'shipped_at',
    'display_name' => 'Shipped At',
    'type' => 'text',
    'readonly' => true,
];

$structure['fields'][] = [
    'name' => 'total',
    'display_name' => 'Total',
    'type' => 'float',
    'precision' => 2,
    'display_callback' => function () {
        return Currency::format($this->currentItem[$this->currentField['name']]);
    },
    'default' => '0',
    'readonly' => true,
];

$structure = (new Builder($structure))
    ->getStructure();
