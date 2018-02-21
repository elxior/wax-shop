<?php

use Wax\Core\Support\Localization\Currency;

    $structure = array(
        'table' => 'gift_cards',
        'primary_key' => 'id',
        'fields' => array(),
        'settings' => array(
            'page_title' => 'Gift Cards',
            'privilege' => 'Gift Cards',
            'create_new_text' => 'Create New Gift Card',
            'back_link' => 'index.php',
            'back_link_text' => 'main menu',
            'editor_subtitle' => 'gift card information',
            'list_fields' => array('code', 'balance', 'email'),
            'order' => array('code'),
            'order_direction' => array('asc'),
            'search_fields' => array('code', 'email'),
        )
    );

    $structure['fields'][] = array(
        'name' => 'code',
        'display_name' => 'Code',
        'type' => 'text',
        'validate' => array('custom', function ($val) {
            if (preg_match('/[^\d]/', $val)) {
                return 'Code must be numeric';
            }
            if (strlen($val) != 16) {
                return 'Code must be 16 digits';
            }
            return true;
        }),
        'unique' => true
    );

    $structure['fields'][] = array(
        'name' => 'balance',
        'display_name' => 'Balance',
        'type' => 'float',
        'precision' => 2,
        'display_callback' => function () {
            return Currency::format($this->currentItem[$this->currentField['name']]);
        },
        'default' => '0',
        'validate' => array('float', 0, false)
    );

    $structure['fields'][] = array(
        'name' => 'email',
        'display_name' => 'Email',
        'type' => 'text',
        'validate' => array('email', false) // validated but not required
    );

    $structure['fields'][] = array(
        'name' => 'note',
        'display_name' => 'Note to Recipient',
        'type' => 'textarea'
    );
