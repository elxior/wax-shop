<?php

use Wax\Shop\Services\ShopService;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'shop'], function () {
    Route::get('/', 'CatalogController@index')->name('catalogIndex');

    Route::get('email-test', function (ShopService $shopService) {
        return view('shop::mail.order-placed', ['order' => $shopService->getPlacedOrder()->toArray()]);
    });

    Route::group(['prefix' => 'api'], function () {
        Route::get('cart', 'CartApiController@index')->name('api.cart.index');
        Route::post('cart', 'CartApiController@store')->name('api.cart.store');
        Route::patch('cart/{id}', 'CartApiController@update')->name('api.cart.update');
        Route::delete('cart/{id}', 'CartApiController@destroy')->name('api.cart.destroy');

        Route::post('coupon', 'CouponApiController@store')->name('api.coupon.store');
        Route::delete('coupon', 'CouponApiController@destroy')->name('api.coupon.destroy');

        /**
         * Order History
         */
        Route::get('history', 'OrderHistoryApiController@index')
            ->middleware('auth')
            ->name('api.history.index');

        Route::get('history/placed', 'OrderHistoryApiController@getPlaced')
            ->middleware('auth')
            ->name('api.history.placed');

        Route::get('history/{id}', 'OrderHistoryApiController@view')
            ->middleware('auth')
            ->name('api.history.view');

        /**
         * Payment Methods
         */
        Route::resource(
            'paymentmethods',
            'PaymentMethodApiController',
            [
                'only' => ['index', 'store', 'update', 'destroy'],
                'middleware' => 'auth'
            ]
        );

        Route::post(
            'paymentmethods/{paymentmethod}/pay',
            'PaymentMethodApiController@makePayment'
        )->middleware('auth');

        Route::post(
            'paymentmethods/{paymentmethod}/set-shipping-address',
            'PaymentMethodApiController@setShippingAddress'
        )->middleware('auth');
    });

    Route::get('/cart', function () {
        return view('shop::pages.cart');
    });

    Route::get('/checkout', function () {
        return view('shop::pages.checkout');
    });

    Route::get('/checkout-complete', function () {
        return view('shop::pages.checkout-complete');
    });

    Route::get('{slug}', 'CatalogController@show')->name('productDetail');
});

Route::group(['prefix' => 'admin', 'middleware' => 'auth.panel'], function () {
    /**
     * Order Manager
     */
    Route::get('shop/order/{id}', 'Admin\OrdersController@show')
        ->name('orderDetails');
    Route::get('shop/order/{id}/print', 'Admin\OrdersController@print')
        ->name('orderDetails.print');
    Route::post('shop/order/{id}/add-tracking/{shipmentId}', 'Admin\OrdersController@addTracking')
        ->name('orderDetails.addTracking');

    /**
     * Product Modifiers
     */
    Route::get('shop/product-modifiers/{product}', 'Admin\ProductModifiersController@show')
        ->name('admin.productModifiers');
    Route::put('shop/product-modifiers/{product}', 'Admin\ProductModifiersController@update');

    /**
     * Coupons
     */
    Route::group(['prefix' => 'cms/coupons', 'as' => 'coupons::'], function () {
        Route::get('generate', 'Admin\CouponController@showGenerateForm')->name('generate.form');
        Route::post('generate', 'Admin\CouponController@bulkGenerateCoupons')->name('generate');

        Route::get('export', 'Admin\CouponController@bulkExportCoupons')->name('export');

        Route::get('import', 'Admin\CouponController@showImportForm')->name('import.form');
        Route::post('import', 'Admin\CouponController@bulkImportCoupons')->name('import');
    });
});

