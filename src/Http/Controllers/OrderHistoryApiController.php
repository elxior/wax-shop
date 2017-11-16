<?php

namespace Wax\Shop\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use Wax\Shop\Repositories\OrderRepository;

class OrderHistoryApiController extends Controller
{
    protected $orderRepo;

    public function __construct(OrderRepository $orderRepo)
    {
        $this->orderRepo = $orderRepo;
    }

    public function index()
    {
        $orders = $this->orderRepo->getOrderHistory();
        return response()->json($orders);
    }

    public function view(int $id)
    {
        $order = $this->orderRepo->getById($id);

        if (Gate::denies('get-order', $order)) {
            abort(403);
        }

        return response()->json($order);
    }

    public function getPlaced()
    {
        $order = $this->orderRepo->getPlaced();
        return response()->json($order);
    }
}
