<?php

$structure = [
    'table' => 'gift_card_failures',
    'primary_key' => 'id',
    'fields' => [],
    'settings' => [
        'page_title' => 'Gift Card Failures',
        //'create_new_text' => 'Create New Record',
        'allow_add' => false,
        'allow_delete' => true,
        'back_link' => 'index.php',
        'back_link_text' => 'main menu',
        'editor_subtitle' => 'details',
        'list_fields' => ['user_id', 'timestamp', 'ip_address'],
        'search_fields' => ['user_id', 'timestamp', 'ip_address'],
        'order' => ['timestamp'],
        'order_direction' => ['desc'],
        'keys' => ['user_id', 'timestamp', 'ip_address']
    ],
];

$structure['fields'][] = [
    'name' => 'created_at',
    'display_name' => 'Timestamp',
    'type' => 'sqltimestamp',
    'auto_update' => false,
    'readonly' => true,
    'default' => 'CURRENT_TIMESTAMP',
];

$structure['fields'][] = [
    'name' => 'user_id',
    'display_name' => 'User',
    'type' => 'select',
    'bind' => CMS_BIND_ONE_TO_MANY,
    'bind_table' => 'users',
    'bind_key' => 'id',
    'bind_value' => 'email',
    'bind_value_type' => 'text',
    'readonly' => true,
];

$structure['fields'][] = [
    'name' => 'ip_address',
    'display_name' => 'IP Address',
    'type' => 'text',
    'readonly' => true,
];
