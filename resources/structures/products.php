<?php

use App\Wax\Admin\Cms\Cms;
use App\Localization\Currency;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Wax\Core\Support\ConfigurationDatabase;
use Wax\Core\Structures\Builder;

    $settings = new ConfigurationDatabase('Shop Settings');

    $structure = array(
        'table' => 'products',
        'primary_key' => 'id',
        'settings' => array(),
        'fields' => array(),

        'postsave' => function ($id, &$cms) {
            /**
             * set the last-modified timestamp here instead of using the auto_update property so
             * that the user sees the previous timestamp instead of the current time
             */
            DB::table('products')
                ->where('id', $id)
                ->update(['updated_at' => Carbon::now()]);

            // check if any handlers have postsave methods
            $item = $cms->getRecord($id, false);
            if (count($item['handlers'])) {
                $hCms = new Cms('product_handlers');
                foreach ($item['handlers'] as $handlerId) {
                    $handler = $hCms->getRecord($handlerId);
                    if (class_exists($handler['class']) && method_exists($handler['class'], 'postsave')) {
                        $handler['object'] = new $handler['class'];
                        $handler['object']->postsave($id, $cms);
                    }
                }
            }
            return true;
        },

        'actions' => [
            'activeon' => [
                'label' => 'Enabled (on)',
                'callback' => function ($id) {
                    Db::perform('products', ['active' => 1], 'update', 'id=' . (int)$id);
                }
            ],
            'activeoff' => [
                'label' => 'Enabled (off)',
                'callback' => function ($id) {
                    Db::perform('products', ['active' => 0], 'update', 'id=' . (int)$id);
                }
            ],
        ]
    );

    $structure['settings'] = array(
        'page_title' => 'Shop - Products',
        'privilege' => 'Shop - Products', // all product related structures should share this privilege
        'allow_bulk_edit' => false,
        'allow_add' => array('Administrator'),
        'allow_delete' => array('Administrator'),
        'create_new_text' => 'Create New Product',
        'back_link' => 'index.php',
        'back_link_text' => 'main menu',
        'editor_subtitle' => 'product information',
        'list_fields' => array('sku', 'model', 'name', 'images', 'category_id', 'price', 'active'),
        'search_fields' => array('sku', 'model', 'name', 'category_id', 'description'),
        'allow_order' => true,
        'order' => array('brand', 'name'),
        'order_direction' => array('ASC', 'asc'),
        'keys' => array()
    );


    /**
     * Sidebars
     */

    $structure['fields'][] = array(
        'sidebar' => true,
        'group' => 'Inventory',
        'group_layout' => 'vertical',
        'name' => 'msrp',
        'display_name' => 'MSRP',
        'type' => 'float',
        'precision' => 2,
        'display_callback' => function () {
            return Currency::format($this->currentItem[$this->currentField['name']]);
        },
        'default' => '0'
    );

    $structure['fields'][] = array(
        'sidebar' => true,
        'group' => 'Inventory',
        'name' => 'price',
        'display_name' => 'Price',
        'type' => 'float',
        'precision' => 2,
        'display_callback' => function () {
            return Currency::format($this->currentItem[$this->currentField['name']]);
        },
        'default' => '0'
    );

    $structure['fields'][] = array(
        'sidebar' => true,
        'group' => 'Inventory',
        'name' => 'inventory',
        'display_name' => 'Inventory',
        'type' => 'integer',
        'default' => '0',
        'notes' => 'Inv. Tracking: ' . (config('wax.shop.inventory.track') ? 'On' : 'Off')
    );

    $structure['fields'][] = array(
        'group' => 'shipping',
        'group_layout' => 'vertical',
        'sidebar' => true,
        'name' => 'shipping_enable_rate_lookup',
        'display_name' => 'Rate Lookup',
        'notes' => 'from carrier webservice',
        'type' => 'boolean',
        'default' => '1'
    );

    $structure['fields'][] = array(
        'group' => 'shipping',
        'group_layout' => 'vertical',
        'sidebar' => true,
        'name' => 'shipping_flat_rate',
        'display_name' => 'Flat Fee',
        'type' => 'float',
        'precision' => 2,
        'display_callback' => function () {
            return Currency::format($this->currentItem[$this->currentField['name']]);
        },
        'default' => '0'
    );


    $structure['fields'][] = array(
        'group' => 'shipping',
        'group_layout' => 'vertical',
        'sidebar' => true,
        'name' => 'shipping_disable_free_shipping',
        'display_name' => 'Disable Free Shipping',
        'type' => 'boolean',
        'default' => '0',
        'notes' => 'Override "Free Shipping for orders over $X" setting'
    );

    $structure['fields'][] = array(
        'sidebar' => true,
        'name' => 'active',
        'display_name' => 'Enabled',
        'type' => 'boolean',
        'default' => '1'
    );


    $structure['fields'][] = array(
        'group' => 'Flags',
        'group_layout' => 'vertical',
        'sidebar' => true,
        'name' => 'featured',
        'display_name' => 'Featured',
        'type' => 'boolean',
        'default' => '0'
    );

    $structure['fields'][] = array(
        'group' => 'Flags',
        'sidebar' => true,
        'name' => 'taxable',
        'display_name' => 'Taxable',
        'type' => 'boolean',
        'default' => '1'
    );

    $structure['fields'][] = array(
        'group' => 'Flags',
        'sidebar' => true,
        'name' => 'discountable',
        'display_name' => 'Discountable',
        'notes' => 'eg coupons',
        'type' => 'boolean',
        'default' => '1'
    );

    $structure['fields'][] = array(
        'group' => 'Flags',
        'sidebar' => true,
        'name' => 'one_per_user',
        'display_name' => 'One Per User',
        'type' => 'boolean',
        'default' => '0'
    );


    $structure['fields'][] = array(
        'sidebar' => true,
        'type' => 'custom',
        'name' => 'modifiers',
        'display_name' => 'Modifiers',
        'render' => function () {
            $html = '<div style="text-align: center;">';
            $html .= "<input type=\"button\" class=\"cancelLeavePageAlert\" value=\"Edit 'Product Option' modifiers\" onclick=\"cancelLeavePageAlert(); $('#cmsEditForm').attr('action', '?action=field_method&field=modifiers&method=edit'); $('#cmsEditForm').submit();\" />";
            $html .= '<p><em>note: any pending changes will be saved before continuing</em></p>';
            $html .= '</div>';
            return $html;
        },
        'edit' => function () {
            if (false !== ($id = $this->save($this->currentId))) {
                header("Location: " . route('shop::admin.productModifiers', ['product'=> $id]));
                exit;
            }
        }
    );

    $structure['fields'][] = array(
        'group' => 'Statistics',
        'group_layout' => 'vertical',
        'sidebar' => true,
        'name' => 'created_at',
        'display_name' => 'Date Added',
        'type' => 'sqltimestamp',
        'readonly' => true,
        'default' => 'CURRENT_TIMESTAMP',
    );

    $structure['fields'][] = array(
        'group' => 'Statistics',
        'group_layout' => 'vertical',
        'sidebar' => true,
        'name' => 'updated_at',
        'display_name' => 'Last Modified',
        'type' => 'sqltimestamp',
        'readonly' => true,
        'nullable' => true,
    );

    $structure['fields'][] = array(
        'group' => 'Statistics',
        'group_layout' => 'vertical',
        'sidebar' => true,
        'name' => 'rating',
        'display_name' => 'Rating (average)',
        'type' => 'float',
        'precision' => 2,
        'default' => '0',
        'readonly' => true
    );

    $structure['fields'][] = array(
        'group' => 'Statistics',
        'group_layout' => 'vertical',
        'sidebar' => true,
        'name' => 'rating_count',
        'display_name' => '# of Ratings',
        'type' => 'bigint',
        'default' => '0',
        'readonly' => true
    );

    $structure['fields'][] = array(
        'sidebar' => true,
        'name' => 'handlers',
        'display_name' => 'Handlers',
        'type' => 'checkbox',
        'bind' => CMS_BIND_MANY_TO_MANY,
        'bind_table' => 'product_handlers',
        'bind_key' => 'id',
        'bind_value' => 'name',
        'bind_link_table' => 'product_handler_links',
        'bind_link_left_key' => 'product_id',
        'bind_link_right_key' => 'handler_id'
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
        'bind_value' => 'name',
        'allow_add' => true,
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
        'name' => 'category_id',
        'display_name' => 'Category',
        'type' => 'select',
        'bind' => CMS_BIND_ONE_TO_MANY,
        'bind_table' => 'product_categories',
        'bind_key' => 'id',
        'bind_value' => 'breadcrumb',
        'nullable' => true,
    );

    $structure['fields'][] = array(
        'name' => 'short_description',
        'display_name' => 'Short Description',
        'type' => 'textarea',
        'wysiwyg' => true
    );

    $structure['fields'][] = array(
        'name' => 'description',
        'display_name' => 'Full Description',
        'type' => 'textarea',
        'wysiwyg' => true
    );

    $structure['fields'][] = array(
        'name' => 'attributes',
        'display_name' => 'Filtering Attributes',
        'type' => 'structure',
        'structure' => 'product_attributes',
        'bind_key' => 'product_id',
        'bind_value' => 'id'
    );

     /**
     * Shipping weight / Dimensions
     */

    $structure['fields'][] = array(
        'group' => 'Dimensions',
        'name' => 'weight',
        'display_name' => 'Weight',
        'type' => 'float',
        'default' => '0',
        'notes' => $settings->get('SHOP_WEIGHT_IN_LBS') ? 'pounds' : 'ounces'
    );

    $structure['fields'][] = array(
        'group' => 'Dimensions',
        'name' => 'dim_l',
        'display_name' => 'Dim L',
        'type' => 'float',
        'default' => '0',
        'notes' => 'inches'
    );

    $structure['fields'][] = array(
        'group' => 'Dimensions',
        'name' => 'dim_w',
        'display_name' => 'Dim W',
        'type' => 'float',
        'default' => '0'
    );

    $structure['fields'][] = array(
        'group' => 'Dimensions',
        'name' => 'dim_h',
        'display_name' => 'Dim H',
        'type' => 'float',
        'default' => '0'
    );

    $structure['fields'][] = array(
        'name' => 'images',
        'display_name' => 'Images',
        'type' => 'structure',
        'max_records' => 20,
        'structure' => 'product_images',
        'bind_key' => 'product_id',
        'bind_value' => 'image' // use `product_images`.`image` in list view
    );

    $structure['fields'][] = array(
        'name' => 'customization',
        'display_name' => 'Customization',
        'type' => 'structure',
        'structure' => 'product_customization',
        'bind_key' => 'product_id',
        'bind_value' => 'name'
    );

    $structure['fields'][] = array(
        'name' => 'options',
        'display_name' => 'Options',
        'type' => 'structure',
        'max_records' => 3,
        'structure' => 'product_option_links',
        'bind_key' => 'product_id',
        'bind_value' => 'option_id'
    );

    $structure['fields'][] = array(
        'name' => 'reviews',
        'display_name' => 'Reviews',
        'type' => 'structure',
        'structure' => 'product_reviews',
        'bind_key' => 'product_id',
        'bind_value' => 'name'
    );

    $structure['fields'][] = array(
        'name' => 'related_products',
        'display_name' => 'Related Products',
        'type' => 'structurerecord',
        'structure' => 'product_picker',
        'max_records' => 0,
        'bind_key' => 'id',
        'bind_value' => 'name',
        'bind_link_table' => 'product_related',
        'bind_link_left_key' => 'product_id',
        'bind_link_right_key' => 'related_id'
    );

$structure = (new Builder($structure))
    ->makeUrlable(['name'])
    ->getStructure();
