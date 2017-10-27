<?php

    $path = '/res/uploads/shop/downloads';

    $structure = array(
        'table' => 'product_downloads',
        'primary_key' => 'id',
        'settings' => array(),
        'fields' => array()
    );

    $structure['settings'] = array(
        'page_title' => 'Product Downloads',
        'privilege' => 'Shop - Products', // all product related structures should share this privilege
        'allow_bulk_edit' => false,
        'create_new_text' => 'Create New',
        'back_link' => 'index.php',
        'back_link_text' => 'main menu',
        'editor_subtitle' => 'digital download',
        'list_fields' => array('title', 'file'),
        'search_fields' => array('title', 'file'),
        'order' => array('title'),
        'order_direction' => array('ASC'),
        'keys' => array('product_id'),
    );

    $structure['fields'][] = array(
        'name' => 'product_id',
        'display_name' => 'Product',
        'type' => 'hidden',
        'bind' => CMS_BIND_ONE_TO_MANY,
        'bind_table' => 'products',
        'bind_key' => 'id',
        'bind_value' => 'id',
    );

    $structure['fields'][] = array(
        'name' => 'title',
        'display_name' => 'Title',
        'type' => 'text',
        'required' => true,
    );

    $structure['fields'][] = array(
        'name' => 'file',
        'display_name' => 'File',
        'type' => 'file',
        'path' => $path,
        'validate' => true,
        'required' => true,
    );
