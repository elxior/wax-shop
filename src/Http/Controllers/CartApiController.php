<?php

namespace Wax\Shop\Http\Controllers;

use App\Http\Controllers\Controller;
use Wax\Shop\Repositories\OrderRepository;
use Wax\Shop\Services\ShopService;
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
        // This isn't necessary but it helps for debugging the math
        $this->orderRepo
            ->getActive()
            ->calculateDiscounts();

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
            $request->input('options', []),
            $request->input('customizations', [])
        );

        return response()->json($this->shopService->getActiveOrder());
    }

    public function update(int $id, Request $request)
    {
        $this->shopService->updateOrderItemQuantity(
            $id,
            $request->input('quantity')
        );

        return response()->json($this->shopService->getActiveOrder());
    }

    public function destroy($itemId)
    {
        $this->shopService->deleteOrderItem($itemId);

        return response()->json($this->shopService->getActiveOrder());
    }
}
