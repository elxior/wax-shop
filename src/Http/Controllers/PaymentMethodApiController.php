<?php

namespace Wax\Shop\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Wax\Shop\Exceptions\ValidationException;
use Wax\Shop\Models\User\PaymentMethod;
use Wax\Shop\Payment\Repositories\PaymentMethodRepository;

class PaymentMethodApiController extends Controller
{
    protected $repo;

    public function __construct(PaymentMethodRepository $repo)
    {
        $this->repo = $repo;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->buildListResponse();
    }

    protected function buildListResponse()
    {
        return Response::json($this->repo->getAll()->makeEntities());
    }

    /**
     * Store a newly created resource in storage.
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
     * Update the specified resource in storage.
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
     * Remove the specified resource from storage.
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
}
