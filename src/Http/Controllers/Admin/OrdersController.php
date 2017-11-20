<?php

namespace Wax\Shop\Http\Controllers\Admin;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use Wax\Shop\Facades\ShopServiceFacade as ShopService;

class OrdersController extends BaseController
{
    public function show($id = 0)
    {
        $order = ShopService::getOrderById($id);

        $page = [
            'title' => "Order #{$order->sequence}",
            'subtitle' => 'order details subtitle',
        ];


        return view('shop::pages.admin.order-details', [
            'order' => $order, // note: not using toArray() because visibility is dialed in for front-end use.
            'page' => $page,
            'structure' => 'orders',
            'id' => $id,
            'errors' => [],
            'notes' => [],
        ]);
    }
}
