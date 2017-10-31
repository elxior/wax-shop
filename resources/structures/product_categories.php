<?php

use Wax\Shop\Services\ShopAdminService;
use Illuminate\Support\Facades\Auth;
use Wax\Core\Structures\Builder;
use Wax\Core\Support\ConfigurationDatabase;

$shopSettings = new ConfigurationDatabase("Shop Settings");
$image_path = $shopSettings->get('SHOP_PRODUCT_IMAGE_PATH') . '/categories';

$structure = array(
    'table' => 'product_categories',
    'primary_key' => 'id',
    'fields' => array (),
    'settings' => array(
        'page_title' => "Shop - Product Categories",
        'privilege' => 'Shop - Products', // all product related structures should share this privilege
        'create_new_text' => 'Create New Category',
        'back_link' => 'index.php',
        'back_link_text' => 'main menu',
        'editor_subtitle' => 'category',
        'list_fields' => array('breadcrumb'),
        'search_fields' => array('breadcrumb'),
        'order' => array('breadcrumb'),
        'order_direction' => array('asc'),
        'sortable' => true,
        'sortable_list_fields' => array('breadcrumb'),
        'row_level_privileges' => array('superuser'),
        // non-superusers can only edit bottom level categories
        'sql_params' => Auth::user()->superuser ? '' : 'product_categories.parent_id > 0 and product_categories.id not in (select parent_id from (select distinct parent_id from product_categories) as updateHack)' // workaround for the "You can't specify target table 'product_categories' for update in FROM clause" issue
    ),
    'postsave' => function ($id) {
        ShopAdminService::updateCategoryBreadcrumbs();
    }
);


$structure['fields'][] = array(
    'name' => 'parent_id',
    'display_name' => 'Parent Category',
    'type' => 'tree',
    'tree_table' => 'product_categories',
    'tree_key' => 'id',
    'tree_value' => 'name',
    'tree_parent_key' => 'parent_id',
    'validate' => false
);


$structure['fields'][] = array(
    'name' => 'name',
    'display_name' => 'Name',
    'type' => 'text',
    'required' => true,
);

$structure = (new Builder($structure))
    ->makeUrlable(['name'], false)
    ->getStructure();


$structure['fields'][] = array(
    'name' => 'short_description',
    'display_name' => 'Short Description',
    'type' => 'textarea',
    'wysiwyg' => false
);

$structure['fields'][] = array(
    'name' => 'description',
    'display_name' => 'Description',
    'type' => 'textarea',
    'wysiwyg' => true
);


$structure['fields'][] = array(
    'name' => 'image',
    'display_name' => 'Image',
    'type' => 'image',
    'path' => $image_path,
    'image_x' => 1500,
    'image_y' => 1500,
    //'crop' => ['arX' => 660, 'arY' => 303]
);


$structure['fields'][] = array(
    'name' => 'breadcrumb',
    'display_name' => 'Breadcrumb',
    'type' => 'text',
    'readonly' => true,
    'notes' => 'automatically updated on save',
    'display_callback' => function () {
        return "<span style=\"white-space: nowrap\">{$this->currentItem[$this->currentField['name']]}</span>";
    }
);

