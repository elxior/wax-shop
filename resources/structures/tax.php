<?php

$structure = [
    'table' => 'tax',
    'primary_key' => 'id',
    'fields' => [],
    'settings' => [
        'page_title' => 'Tax Table',
        'privilege' => 'Tax Table',
        'create_new_text' => 'Create New Record',
        'back_link' => 'index.php',
        'back_link_text' => 'main menu',
        'editor_subtitle' => 'record information',
        'list_fields' => ['zone', 'rate', 'tax_shipping'],
        'search_fields' => ['zone'],
        'order' => ['zone'],
        'order_direction' => ['asc'],
        'allow_add' => true,
        'keys' => ['zone'],
    ],
];
$structure['fields'][] = [
    'name' => 'zone',
    'display_name' => 'State',
    'type' => 'text',
    'validate' => ['string', 2, 2]
];
$structure['fields'][] = [
    'group' => 'Tax Rate',
    'name' => 'rate',
    'display_name' => 'Tax Rate',
    'type' => 'float',
    'validate' => ['float', 0, false],
    'display_callback' => function () {
        return "{$this->currentItem[$this->currentField['name']]}%";
    },
    'notes' => 'percent'
];
$structure['fields'][] = [
    'group' => 'Tax Rate',
    'name' => 'tax_shipping',
    'display_name' => 'Tax Shipping?',
    'type' => 'boolean',
    'default' => 1
];
