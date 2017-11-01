<?php

$structure = [
    'table' => 'product_bundles',
    'primary_key' => 'id',
    'fields' => [],

    'settings' => [
        'page_title' => 'Product Bundles',
        'create_new_text' => 'Create New Bundle',
        'back_link' => 'index.php',
        'back_link_text' => 'main menu',
        'editor_subtitle' => 'bundle information',
        'list_fields' => ['name', 'percent', 'products'],
        'order' => ['name'],
        'order_direction' => ['asc'],
        'search_fields' => ['name', 'products'],
    ]
];

$structure['fields'][] = [
    'name' => 'name',
    'type' => 'text',
    'required' => true,
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
    'validate' => ['integer', 0, 50],
    'required' => true,
];

$structure['fields'][] = array(
    'name' => 'products',
    'display_name' => 'Related Products',
    'type' => 'structurerecord',
    'structure' => 'product_picker',
    'max_records' => 0,
    'bind_key' => 'id',
    'bind_value' => 'name',
    'bind_link_table' => 'product_bundle_links',
    'bind_link_left_key' => 'bundle_id',
    'bind_link_right_key' => 'product_id'
);
