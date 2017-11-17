<?php

use Illuminate\Support\Facades\DB;
use Wax\Core\Support\ConfigurationDatabase;
use Wax\Shop\Models\Product\Image;

    $shopSettings = new ConfigurationDatabase("Shop Settings");
    $image_path = $shopSettings->get('SHOP_PRODUCT_IMAGE_PATH');

    $structure = array(
        'table' => 'product_images',
        'primary_key' => 'id',
        'settings' => array(),
        'fields' => array(),
        'presave' => function () {
            if ($this->data->field('default') == 1) {
                Image::where('product_id', $this->data->variable('product_id'))
                    ->update(['default' => 0]);
            }
            return true;
        },
        'postsave' => function () {

            // if there are images then one must be set to default
            $count = DB::table('product_images')
                ->where('product_id', $this->data->variable('product_id'))
                ->where('default', 1)
                ->count();

            if ($count == 0) {
                Image::where('product_id', $this->data->variable('product_id'))
                    ->take(1)
                    ->update(['default' => 1]);
            }
        }
    );

    $structure['settings'] = array(
        'page_title' => 'Product Images',
        'privilege' => 'Shop - Products', // all product related structures should share this privilege
        'allow_bulk_edit' => false,
        'create_new_text' => 'Create New',
        'back_link' => 'index.php',
        'back_link_text' => 'main menu',
        'editor_subtitle' => 'image',
        'list_fields' => array('image', 'caption', 'default'),
        'search_fields' => array('image', 'caption'),
        'order' => array('image'),
        'order_direction' => array('ASC'),
        'keys' => array('product_id'),
        'sortable' => true,
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
        'name' => 'image',
        'display_name' => 'Image',
        'type' => 'image',
        'path' => $image_path . '/full',
        'image_x' => 3000,
        'image_y' => 3000,
        'crop' => array(
            'arX' => 2,
            'arY' => 1,
        ),
        'copy' => array(
            'large' => array(
                'crop' => true,
                'path' => $image_path . "/large",
                'image_x' => 800,
                'image_y' => 400,
            ),
            'small' => array(
                'path' => $image_path . "/small",
                'image_x' => 400,
                'image_y' => 200,
            ),
        ),
        'validate' => true,
        'required' => true,
    );

    $structure['fields'][] = array(
        'name' => 'default',
        'display_name' => 'Default Image',
        'type' => 'boolean',
        'default' => 0
    );

    $structure['fields'][] = array(
        'name' => 'caption',
        'display_name' => 'Caption',
        'type' => 'text',
    );
