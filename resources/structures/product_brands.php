<?php

$structure = [
    'table' => 'product_brands',
    'primary_key' => 'id',
    'settings' => [],
    'fields' => []
];

$structure['settings'] = [
    'page_title' => 'Shop - Brands',
    'privilege' => 'Shop - Products', // all product related structures should share this privilege
    'allow_bulk_edit' => false,
    'create_new_text' => 'Create New',
    'back_link' => 'index.php',
    'back_link_text' => 'main menu',
    'editor_subtitle' => 'brand',
    'list_fields' => ['name'],
    'search_fields' => ['name'],
    'order' => ['name'],
    'order_direction' => ['ASC'],
];

$structure['fields'][] = [
    'name' => 'name',
    'display_name' => 'Name',
    'type' => 'text',
    'required' => true,
];
