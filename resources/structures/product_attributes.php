<?php

$structure = [
    'table' => 'product_attribute_links',
    'primary_key' => 'id',
    'settings' => [],
    'fields' => [],
];

$structure['settings'] = [
    'page_title' => 'Shop - Product Attributes',
    'privilege' => 'Shop - Products', // all product related structures should share this privilege
    'allow_bulk_edit' => false,
    'create_new_text' => 'Create New',
    'back_link' => 'index.php',
    'back_link_text' => 'main menu',
    'editor_subtitle' => 'Attribute',
    'list_fields' => ['name_id', 'value_id'],
    'search_fields' => ['name_id', 'value_id'],
    'sortable' => true
];


$structure['fields'][] = [
    'name' => 'product_id',
    'display_name' => 'Product',
    'type' => 'hidden',
    'bind' => CMS_BIND_ONE_TO_MANY,
    'bind_table' => 'products',
    'bind_key' => 'id',
    'bind_value' => 'id',
];

$structure['fields'][] = [
    'name' => 'name_id',
    'display_name' => 'Attr. Name',
    'type' => 'select',
    'bind' => CMS_BIND_ONE_TO_MANY,
    'bind_table' => 'product_attribute_names',
    'bind_key' => 'id',
    'bind_value' => 'name',
    'allow_add' => true
];

$structure['fields'][] = [
    'name' => 'value_id',
    'display_name' => 'Value',
    'type' => 'select',
    'bind' => CMS_BIND_ONE_TO_MANY,
    'bind_table' => 'product_attribute_values',
    'bind_key' => 'id',
    'bind_value' => 'value',
    'allow_add' => true
];
