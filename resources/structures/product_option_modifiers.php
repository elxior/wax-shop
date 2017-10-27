<?php

    $structure = array(
        'table' => 'product_option_modifiers',
        'primary_key' => 'id',
        'fields' => array (),
        'settings' => array(
            'page_title' => "Shop - Product Option Modifiers",
            'privilege' => 'Shop - Products', // all product related structures should share this privilege
            //'create_new_text' => 'Create Product Option Values',
            'allow_add' => false,
            'back_link' => 'index.php',
            'back_link_text' => 'main menu',
            'editor_subtitle' => 'options',

            'list_fields' => array('product_id', 'values', 'sku', 'price', 'inventory'),
            'order' => array('product_id'),
            'search_fields' => array('product_id'),
        ),
    );

    $structure['fields'][] = array(
        'name' => 'product_id',
        'display_name' => 'Product',
        'type' => 'select',
        'bind' => CMS_BIND_ONE_TO_MANY,
        'bind_table' => 'products',
        'bind_key' => 'id',
        'bind_value' => 'name',
    );

    $structure['fields'][] = array(
        'name' => 'values',
        'display_name' => 'Values',
        'type' => 'text',
        'required' => true,
    );

    $structure['fields'][] = array(
        'name' => 'sku',
        'display_name' => 'SKU',
        'type' => 'text',
    );

    $structure['fields'][] = array(
        'name' => 'price',
        'display_name' => 'Price',
        'type' => 'float',
        'precision' => 2,
        'display_callback' => function () {
            return money_format("%n", $this->currentItem[$this->currentField['name']]);
        },
        'default' => '0'
    );

    $structure['fields'][] = array(
        'name' => 'inventory',
        'display_name' => 'Inventory',
        'type' => 'integer',
    );
