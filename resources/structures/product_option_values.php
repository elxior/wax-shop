<?php

    $structure = array(
        'table' => 'product_option_values',
        'primary_key' => 'id',
        'fields' => array (),
        'settings' => array(
            'page_title' => "Shop - Product Option Values",
            'privilege' => 'Shop - Products', // all product related structures should share this privilege
            'create_new_text' => 'Create Product Option Values',
            'back_link' => 'index.php',
            'back_link_text' => 'main menu',
            'editor_subtitle' => 'option information',
            'list_fields' => array('name'),
            'order' => array('name'),
            'search_fields' => array('name'),
        ),
    );

    $structure['fields'][] = array(
        'name' => 'option_id',
        'display_name' => 'Option',
        'type' => 'hidden',
        'bind' => CMS_BIND_ONE_TO_MANY,
        'bind_table' => 'product_options',
        'bind_key' => 'id',
        'bind_value' => 'id',
    );

    $structure['fields'][] = array(
        'name' => 'name',
        'display_name' => 'Name',
        'type' => 'text',
        'required' => true,
    );
