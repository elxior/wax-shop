<?php

namespace Wax\Shop\Http\Controllers\Admin;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use Wax\Shop\Facades\ShopServiceFacade as ShopService;

class OrdersController extends BaseController
{
    public function show($id)
    {
        $order = ShopService::getOrderById($id);

        $page = [
            'title' => "Order Details",
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

    public function print($id)
    {
        $order = ShopService::getOrderById($id);

        return view('shop::pages.admin.order-details-print', [
            'order' => $order,
        ]);
    }

    public function addTracking(Request $request, $id, $shipmentId)
    {
        $order = ShopService::getOrderById($id);

        $shipment = $order->shipments()->where('id', $shipmentId)->first();

        if (!$shipment) {
            abort(400);
        }

        $shipment->setTrackingNumber($request->input('tracking_number'));

        $page = [
            'title' => "Order Details",
        ];

        return view('shop::pages.admin.order-details', [
            'order' => $order, // note: not using toArray() because visibility is dialed in for front-end use.
            'page' => $page,
            'structure' => 'orders',
            'id' => $id,
            'errors' => ['The shipment has been marked as shipped and the customer has been notified.'],
            'notes' => [],
        ]);
    }
}
