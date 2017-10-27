<?php

    $structure = array(
        'table' => 'product_option_value_links',
        'primary_key' => 'id',
        'fields' => array (),
        'settings' => array(
            'page_title' => "Shop - Product-Option-Value Links",
            'privilege' => 'Shop - Products', // all product related structures should share this privilege
            'create_new_text' => 'Create Link',
            'back_link' => 'index.php',
            'back_link_text' => 'main menu',
            'editor_subtitle' => 'option information',
            'list_fields' => array('product_id', 'value_id'),
            'order' => array('product_id', 'value_id'),
            'search_fields' => array('product_id', 'value_id'),
        )
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
        'name' => 'value_id',
        'display_name' => 'Value',
        'type' => 'select',
        'bind' => CMS_BIND_ONE_TO_MANY,
        'bind_table' => 'product_option_values',
        'bind_key' => 'id',
        'bind_value' => 'name',
    );
