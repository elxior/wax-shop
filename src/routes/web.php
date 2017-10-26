<?php

use App\WindowData\Http\Middleware\CatalogDataMiddleware;
use Illuminate\Support\Facades\Route;

/**
 * Note: routes are separated into this file in anticipation of moving to a wax-cms package
 */

Route::get('/admin/shop/product-modifiers/{product}', 'Admin\ProductModifiersController@show')
    ->middleware('auth.panel')
    ->name('admin.productModifiers');
Route::put('/admin/shop/product-modifiers/{product}', 'Admin\ProductModifiersController@update')
    ->middleware('auth.panel');

Route::group(['prefix' => 'shop'], function () {
    Route::get('/', 'CatalogController@index')->middleware(CatalogDataMiddleware::class)->name('catalogIndex');
    Route::get('{slug}', 'CatalogController@show')->name('productDetail');


    Route::group(['prefix' => 'api'], function () {
        Route::get('cart', 'CartApiController@index')->name('api.cart.index');
        Route::post('cart', 'CartApiController@store')->name('api.cart.store');
        Route::delete('cart/{id}', 'CartApiController@destroy')->name('api.cart.destroy');
    });

    Route::get('/cart', function () {
        return view('modules.Shop.pages.cart');
    });

    Route::get('/checkout', function () {
        return view('modules.Shop.pages.checkout');
    });

    Route::get('/checkout-complete', function () {
        return view('modules.Shop.pages.checkout-complete');
    });
});
