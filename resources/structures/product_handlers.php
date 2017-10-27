<?php

    $structure = array(
        'table' => 'product_handlers',
        'primary_key' => 'id',
        'settings' => array(),
        'fields' => array()
    );

    $structure['settings'] = array(
        'page_title' => 'Product Handlers',
        'privilege' => 'Shop - Products', // all product related structures should share this privilege
        'allow_bulk_edit' => false,
        'create_new_text' => 'Create New Handler',
        'back_link' => 'index.php',
        'back_link_text' => 'main menu',
        'editor_subtitle' => 'image',
        'list_fields' => array('name'),
        'search_fields' => array('name', 'class'),
        'order' => array('name'),
        'order_direction' => array('ASC'),
    );

    $structure['fields'][] = array(
        'name' => 'name',
        'display_name' => 'Name',
        'type' => 'text',
        'validate' => true
    );

    $structure['fields'][] = array(
        'name' => 'class',
        'display_name' => 'Class Name',
        'type' => 'text',
        'validate' => true
    );
