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
        return Response::json($this->buildListResponse());
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

        return Response::json($this->buildListResponse());
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

        return Response::json($this->buildListResponse());
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

        return Response::json($this->buildListResponse());
    }

    /**
     * Make a payment.
     *
     * @param PaymentMethod $paymentMethod
     * @return \Illuminate\Http\Response
     * @throws ValidationException
     */
    public function makePayment(PaymentMethod $paymentMethod)
    {
        if (Auth::user()->cant('pay', $paymentMethod)) {
            return response()->json(['_error' => [__('shop::payment.make_payment_unauthorized')]], 403);
        }

        $payment = $this->shopService->makeStoredPayment($paymentMethod);

        return response()->json($payment);
    }

    public function setShippingAddress(PaymentMethod $paymentMethod)
    {
        if (Auth::user()->cant('view', $paymentMethod)) {
            return response()->json(['_error' => [__('shop::payment.set_shipping_address')]], 403);
        }

        $order = $this->shopService->getActiveOrder();
        $this->repo->useAddressForShipping($order, $paymentMethod);

        return response()->json($this->shopService->getActiveOrder());
    }

    protected function buildListResponse()
    {
        return $this->repo->getAll();
    }
}
