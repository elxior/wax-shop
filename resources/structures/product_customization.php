<?php

    $structure = array(
        'table' => 'product_customization',
        'primary_key' => 'id',
        'settings' => array(),
        'fields' => array(),
        'notes' => 'Customization creates user input fields on the product page.  The exact behavior of the validation parameters depends upon which input type is selected.'
    );

    $structure['settings'] = array(
        'page_title' => 'Product Customization',
        'privilege' => 'Shop - Products', // all product related structures should share this privilege
        'allow_bulk_edit' => false,
        'create_new_text' => 'Create New Customization',
        'back_link' => 'index.php',
        'back_link_text' => 'main menu',
        'editor_subtitle' => 'customization',
        'list_fields' => array('name', 'type', 'required'),
        'search_fields' => array('name', 'type'),
        'order' => array('name'),
        'order_direction' => array('asc'),
        'keys' => array('product_id'),
        'sortable' => true
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
        'name' => 'name',
        'display_name' => 'Name / Input Label',
        'type' => 'text',
        'required' => true
    );

    $structure['fields'][] = array(
        'name' => 'type',
        'display_name' => 'Type',
        'type' => 'radio',
        'values' => array(
            'text' => 'Text',
            'textarea' => 'Textarea',
            'email' => 'Email',
            'number' => 'Number',
            //'money' => 'Money'
        ),
        'default' => 'text',
        'required' => true
    );

    $structure['fields'][] = array(
        'group' => 'Validation',
        'name' => 'required',
        'display_name' => 'Required',
        'type' => 'boolean',
        'default' => 1
    );

    $structure['fields'][] = array(
        'group' => 'Validation',
        'name' => 'min',
        'display_name' => 'Minimum length / value',
        'type' => 'text'
    );

    $structure['fields'][] = array(
        'group' => 'Validation',
        'name' => 'max',
        'display_name' => 'Maximum length / value',
        'type' => 'text'
    );
