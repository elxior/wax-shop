<?php

namespace Wax\Shop\Http\Controllers\Admin;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use Wax\Shop\Facades\ShopServiceFacade as ShopService;

class OrdersController extends BaseController
{
    public function show($id = 0)
    {
        $page = [
            'title' => 'Order Details',
            'subtitle' => 'order details subtitle',
        ];

        $order = ShopService::getOrderById($id);

        return view('pages.admin.order-details', [
            'order' => $order->toArray(),
            'page' => $page,
            'structure' => 'orders',
            'id' => $id,
            'errors' => [],
            'notes' => [],

        ]);
    }
}
