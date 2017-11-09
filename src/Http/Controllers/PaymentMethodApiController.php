<?php

namespace Wax\Shop\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Wax\Shop\Exceptions\ValidationException;
use Wax\Shop\Models\User\PaymentMethod;
use Wax\Shop\Payment\Repositories\PaymentMethodRepository;
use Wax\Shop\Services\ShopService;

class PaymentMethodApiController extends Controller
{
    protected $repo;
    protected $shopService;

    public function __construct(ShopService $shopService, PaymentMethodRepository $repo)
    {
        $this->repo = $repo;
        $this->shopService = $shopService;
    }

    /**
     * List the user's PaymentMethods.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->buildListResponse();
    }

    /**
     * Create a new PaymentMethod.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     * @throws ValidationException
     */
    public function store(Request $request)
    {
        $this->repo->create($request->only([
            'cardNumber',
            'expMonth',
            'expYear',
            'cvc',
            'firstName',
            'lastName',
            'address',
            'zip',
        ]));

        return $this->buildListResponse();
    }

    /**
     * Update a PaymentMethod.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  PaymentMethod  $paymentMethod
     * @return \Illuminate\Http\Response
     * @throws ValidationException
     */
    public function update(Request $request, PaymentMethod $paymentMethod)
    {
        if (Auth::user()->cant('update', $paymentMethod)) {
            abort(403);
        }

        $this->repo->update($request->only([
            'cardNumber',
            'expMonth',
            'expYear',
            'cvc',
            'firstName',
            'lastName',
            'address',
            'zip',
        ]), $paymentMethod);

        return $this->buildListResponse();
    }

    /**
     * Delete a PaymentMethod.
     *
     * @param  PaymentMethod  $paymentMethod
     * @return \Illuminate\Http\Response
     */
    public function destroy(PaymentMethod $paymentMethod)
    {
        if (Auth::user()->cant('delete', $paymentMethod)) {
            abort(403);
        }

        $this->repo->delete($paymentMethod);

        return $this->buildListResponse();
    }

    /**
     * Make a payment.
     *
     * @param Request $request
     * @param PaymentMethod $paymentMethod
     * @return \Illuminate\Http\Response
     */
    public function makePayment(Request $request, PaymentMethod $paymentMethod)
    {
        if (Auth::user()->cant('pay', $paymentMethod)) {
            abort(403);
        }

        $order = $this->shopService->getActiveOrder();

        $payment = $this->repo->makePayment($order, $paymentMethod, $order->balanceDue);



        return response()->json($payment);
    }

    protected function buildListResponse()
    {
        return Response::json($this->repo->getAll());
    }
}
