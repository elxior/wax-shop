<?php

$structure = [
    'table' => 'product_attribute_values',
    'primary_key' => 'id',
    'settings' => [],
    'fields' => [],
];

$structure['settings'] = [
    'page_title' => 'Shop - Product Attribute Values',
    'privilege' => 'Shop - Products', // all product related structures should share this privilege
    'allow_bulk_edit' => false,
    'create_new_text' => 'Create New',
    'back_link' => 'index.php',
    'back_link_text' => 'main menu',
    'editor_subtitle' => 'Attribute Values',
    'list_fields' => ['value'],
    'search_fields' => ['value'],
    'sortable' => true,
];

$structure['fields'][] = [
    'name' => 'value',
    'display_name' => 'Value',
    'type' => 'text',
    'required' => true
];
