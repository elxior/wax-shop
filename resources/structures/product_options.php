<?php

    $structure = array(
        'table' => 'product_options',
        'primary_key' => 'id',
        'fields' => array (),
        'settings' => array(
            'page_title' => "Shop - Product Options",
            'privilege' => 'Shop - Products', // all product related structures should share this privilege
            'create_new_text' => 'Create Product Options',
            'back_link' => 'index.php',
            'back_link_text' => 'main menu',
            'editor_subtitle' => 'option information',
            'list_fields' => array('name', 'values'),
            'order' => array('name'),
            'search_fields' => array('name', 'values'),
        ),
    );

    $structure['fields'][] = array(
        'name' => 'name',
        'display_name' => 'Name',
        'type' => 'text',
        'required' => true,
    );

    $structure['fields'][] = array(
        'name' => 'values',
        'display_name' => 'Values',
        'type' => 'structure',
        'structure' => 'product_option_values',
        'bind_key' => 'option_id',
        'bind_value' => 'name'
    );
