<?php

namespace App\Shop\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Shop\Repositories\OrderRepository;
use App\Shop\Services\ShopService;
use Illuminate\Http\Request;

class CartApiController extends Controller
{
    protected $shopService;
    protected $orderRepo;

    public function __construct(ShopService $shopService, OrderRepository $orderRepo)
    {
        $this->shopService = $shopService;
        $this->orderRepo = $orderRepo;
    }

    public function index()
    {
        $this->orderRepo
            ->getActive()
            ->default_shipment
            ->combineDuplicateItems();

        return response()->json($this->shopService->getActiveOrder());
    }

    public function store(Request $request)
    {
        $this->shopService->addOrderItem(
            $request->input('product_id'),
            $request->input('quantity'),
            $request->input('options', [])
        );

        return response()->json($this->shopService->getActiveOrder());
    }

    public function destroy($itemId)
    {
        $this->shopService->deleteOrderItem($itemId) || abort(400);

        return response()->json($this->shopService->getActiveOrder());
    }



}