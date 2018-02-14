<?php

namespace Wax\Shop\Http\Controllers;

use App\Http\Controllers\Controller;
use Wax\Shop\Repositories\OrderRepository;
use Wax\Shop\Services\ShopService;
use Illuminate\Http\Request;

class CouponApiController extends Controller
{
    protected $shopService;
    protected $orderRepo;

    public function __construct(ShopService $shopService, OrderRepository $orderRepo)
    {
        $this->shopService = $shopService;
        $this->orderRepo = $orderRepo;
    }

    public function store(Request $request)
    {
        if (!$this->shopService->applyCoupon($request->input('code'))) {
            return response()->json(['code' => [__('shop::coupon.invalid_code')]], 422);
        }

        return response()->json($this->shopService->getActiveOrder());
    }

    public function destroy()
    {
        $this->shopService->removeCoupon();

        return response()->json($this->shopService->getActiveOrder());
    }
}
