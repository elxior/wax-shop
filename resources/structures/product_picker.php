<?php

use Illuminate\Support\Facades\DB;
use Wax\Core\Support\ConfigurationDatabase;

    $image_path = '/res/uploads/shop/products/images';

    $structure = array(
        'table' => 'products',
        'primary_key' => 'id',
        'settings' => array(),
        'fields' => array(),

    );

    $structure['settings'] = array(
        'page_title' => 'Shop - Product Manager',
        'privilege' => 'Shop - Products', // all product related structures should share this privilege
        'allow_bulk_edit' => false,
        'allow_add' => array('Administrator'),
        'allow_delete' => array('Administrator'),
        'create_new_text' => 'Create New Product',
        'back_link' => 'index.php',
        'back_link_text' => 'main menu',
        'editor_subtitle' => 'product information',
        'list_fields' => array('images', 'sku', 'name', 'attributes', 'price'),
        //'search_fields' => array('sku', 'brand', 'name', 'images', 'price'),
        'allow_order' => true,
        'order' => array('brand', 'name'),
        'order_direction' => array('ASC', 'asc'),
        'sql_params' => 'active=1'
    );


    /**
     * Sidebars
     */

    $structure['fields'][] = array(
        'sidebar' => true,
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
        'group' => 'status',
        'sidebar' => true,
        'name' => 'active',
        'display_name' => 'Enabled',
        'type' => 'boolean',
        'default' => '1'
    );


    /**
     * Main section
     */


    /**
     * Product Identification Group
     */

    $structure['fields'][] = array(
        'group' => 'id',
        'name' => 'sku',
        'display_name' => 'SKU',
        'type' => 'text',
        'validate' => true,
        'unique' => true

    );

    $structure['fields'][] = array(
        'group' => 'id',
        'name' => 'brand',
        'display_name' => 'Brand',
        'type' => 'select',
        'bind' => CMS_BIND_ONE_TO_MANY,
        'bind_table' => 'product_brands',
        'bind_key' => 'id',
        'bind_value' => 'name'
    );

    $structure['fields'][] = array(
        'group' => 'id',
        'name' => 'model',
        'display_name' => 'Model',
        'type' => 'text'
    );


    /**
     * Basic Info
     */

    $structure['fields'][] = array(
        'name' => 'name',
        'display_name' => 'Name',
        'type' => 'text',
        'required' => true,
    );


    $structure['fields'][] = array(
        'name' => 'images',
        'display_name' => 'Images',
        'type' => 'structure',
        'max_records' => 1,
        'structure' => 'product_images',
        'bind_key' => 'product_id',
        'bind_value' => 'image' // use `products_images`.`image` in list view
    );

    $structure['fields'][] = array(
        'group' => 'Specifications',
        'group_layout' => 'vertical',
        'name' => 'attributes',
        'display_name' => 'Filtering Attributes',
        'type' => 'structure',
        'structure' => 'product_attributes',
        'bind_key' => 'product_id',
        'bind_value' => 'id',
        'display_callback' => function () {
            return $attrs = DB::table('product_attribute_values as pav')
                ->join('product_attribute_links as pal', 'pav.id', '=', 'pal.value_id')
                ->whereIn('pal.id', $this->currentItem['attributes'])
                ->pluck('value')
                ->implode(', ');
        }
    );
