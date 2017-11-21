<?php
$structure = [
    'table' => 'user_payment_methods',
    'primary_key' => 'id',
    'settings' => [
        'page_title' => 'Users: Payment Methods',
        'privilege' => 'Users',
        'allow_add' => false,
        'back_link' => 'index.php',
        'back_link_text' => 'main menu',
        'editor_subtitle' => 'Payment Method',
        'list_fields' => ['brand', 'masked_card_number', 'expiration_date'],
        'readonly' => true
    ],
];

$structure['fields'][] = [
    'name' => 'user_id',
    'type' => 'hidden',
    'heal_type' => 'integer',
    'bind_table' => 'users',
];

$structure['fields'][] = [
    'name' => 'payment_profile_id',
    'type' => 'text',
];

$structure['fields'][] = [
    'group' => 'Payment Method',
    'name' => 'brand',
    'type' => 'text',
];

$structure['fields'][] = [
    'group' => 'Payment Method',
    'display_name' => 'Account Number',
    'name' => 'masked_card_number',
    'type' => 'text',
    'notes' => 'last 4 digits',
];

$structure['fields'][] = [
    'group' => 'Payment Method',
    'name' => 'expiration_date',
    'type' => 'text',
];

$structure['fields'][] = [
    'group' => 'Name',
    'name' => 'firstname',
    'display_name' => 'First Name',
    'type' => 'text',
];

$structure['fields'][] = [
    'group' => 'Name',
    'name' => 'lastname',
    'display_name' => 'Last Name',
    'type' => 'text',
];

$structure['fields'][] = [
    'name' => 'address',
    'type' => 'text',
];

$structure['fields'][] = [
    'name' => 'zip',
    'type' => 'text',
];
