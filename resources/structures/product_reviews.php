<?php

use Illuminate\Support\Facades\DB;

$embedded = preg_match('/^products(\.php)$/', request('structure')) || preg_match('/^products(\.php)$/', request('parentStructure'));

$structure = array(
    'table' => 'product_reviews',
    'primary_key' => 'id',
    'settings' => array(),
    'fields' => array(),
    'postsave' => function ($id, &$cms) {

        // To which product does the review apply?
        $productId = DB::table('product_reviews')
            ->where('id', $id)
            ->value('product_id');

        // Get all reviews on the product.
        $reviews = DB::table('product_reviews')
            ->where('approved', 1)
            ->where('product_id', $productId);

        $ratingSum = $reviews->sum('rating');
        $ratingCount = $reviews->count();

        DB::table('products')
            ->where('id')
            ->update([
                'rating' => $ratingSum / $ratingCount,
                'rating_count' => $ratingCount,
            ]);

    }
);

$structure['settings'] = array(
    'page_title' => 'Product Reviews',
    'privilege' => 'Shop - Products', // all product related structures should share this privilege
    'allow_bulk_edit' => false,
    'allow_add' => $embedded,
    'create_new_text' => 'Create New',
    'back_link' => 'index.php',
    'back_link_text' => 'main menu',
    'editor_subtitle' => 'review',
    'list_fields' => array('created_at', 'name', 'location', 'rating', 'approved_at'),
    'search_fields' => array('name', 'location'),
    'order' => array('created_at'),
    'order_direction' => array('desc'),
    'keys' => array('product_id'),
    'allow_order' => true
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
    'name' => 'link',
    'display_name' => 'Product',
    'type' => 'custom',
    'render' => function () {
        return 'fixme: product_reviews -> link';
//        $parentId = (int)request('parentId');
//        if ($parentId ==0) {
//            if (false !== ($review = $this->getRecord($this->currentItem[$this->structure['primary_key']]))) {
//                $parentId = $review['product_id'];
//            }
//        }
//        if ($parentId > 0) {
//            $shop = &Shop::getInstance();
//            if (false !== ($product = $shop->getProduct($parentId))) {
//                $link = '<a href="' . Html::encode($product['url']) . '" target="_blank">' . Html::encode($product['name']) . '</a>';
//                return $link;
//            }
//        }
//        return false;
    }
);

/**
 * Sidebar
 */

$structure['fields'][] = array(
    'sidebar' => true,
    'name' => 'approved_at',
    'display_name' => 'Approved At',
    'type' => 'sqltimestamp',
    'display_callback' => function () {
        return is_null($this->currentItem[$this->currentField['name']]) ? 'Approved' : 'Pending';
    },
    'nullable' => true,
);


$structure['fields'][] = array(
    'sidebar' => true,
    'name' => 'created_at',
    'display_name' => 'Date Submitted',
    'type' => 'sqltimestamp',
    'readonly' => true,
    'default' => 'CURRENT_TIMESTAMP',
);

/**
 * Main
 */

$structure['fields'][] = array(
    'group' => 'Submission Info',
    'name' => 'name',
    'display_name' => 'Name',
    'type' => 'text',
    'validate' => true
);

$structure['fields'][] = array(
    'group' => 'Submission Info',
    'name' => 'location',
    'display_name' => 'Location',
    'type' => 'text',
);

// rating range
$range = array_map(function ($x) {
    return number_format($x, 1);
}, range(config('wax.shop.ratings.min'), config('wax.shop.ratings.max'), config('wax.shop.ratings.increment')));

$structure['fields'][] = array(
    'group' => 'Submission Info',
    'name' => 'rating',
    'display_name' => 'Rating',
    'type' => 'select',
    'values' => array_combine($range, $range),
    'validate' => true
);

$structure['fields'][] = array(
    'name' => 'review',
    'display_name' => 'Review',
    'type' => 'textarea',
);

$structure['fields'][] = array(
    'group' => 'Visitor Info',
    'name' => 'ip_address',
    'display_name' => 'IP Address',
    'type' => 'text',
    'readonly' => true
);

$structure['fields'][] = array(
    'group' => 'Visitor Info',
    'name' => 'user_id',
    'display_name' => 'User Account',
    'type' => 'select',
    'bind' => CMS_BIND_ONE_TO_MANY,
    'bind_table' => 'users',
    'bind_key' => 'id',
    'bind_value' => 'email',
    'bind_value_type' => 'text',
    'readonly' => true,
);
