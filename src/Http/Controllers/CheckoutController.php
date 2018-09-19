<?php

namespace Wax\Shop\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Wax\Shop\Exceptions\ValidationException;
use Wax\Shop\Services\ShopService;

class CheckoutController extends Controller
{
    protected $shopService;

    public function __construct(ShopService $shopService)
    {
        $this->shopService = $shopService;
    }

    /**
     * Place an order. This would be used when the order is completely set up and payments/discounts have been
     * applied to bring the balance due to $0.00.
     *
     * @return Response
     * @throws ValidationException
     */
    public function placeOrder()
    {
        $this->shopService->placeOrder();

        return response()->json(true);
    }
}
