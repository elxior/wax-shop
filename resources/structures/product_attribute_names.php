<?php

$structure = [
    'table' => 'product_attribute_names',
    'primary_key' => 'id',
    'settings' => [],
    'fields' => [],
];

$structure['settings'] = [
    'page_title' => 'Shop - Product Attribute Names',
    'privilege' => 'Shop - Products', // all product related structures should share this privilege
    'allow_bulk_edit' => false,
    'create_new_text' => 'Create New',
    'back_link' => 'index.php',
    'back_link_text' => 'main menu',
    'editor_subtitle' => 'Attribute Names',
    'list_fields' => ['name'],
    'search_fields' => ['name'],
    'sortable' => true,
];

$structure['fields'][] = array(
    'name' => 'name',
    'display_name' => 'Name',
    'type' => 'text',
    'required' => true
);