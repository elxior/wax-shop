<?php

namespace Wax\Shop\Http\Controllers;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\MessageBag;
use Wax\Shop\Exceptions\ValidationException;
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
        return Response::json($this->repo->getBillingInfo()->makeEntities());
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
     * @param  Model  $billingInfo
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Model $billingInfo)
    {
        if (Auth::user()->cant('update', $billingInfo)) {
            abort(403);
        }

        try {
            $this->repo->update($request->only([
                'cardNumber',
                'expMonth',
                'expYear',
                'cvc',
                'firstName',
                'lastName',
                'address',
                'zip',
            ]), $billingInfo);
        } catch (Exception $ex) {
            $response = (new MessageBag())->add('general', 'There was a problem with your payment information.');
            return Response::json($response, 400);
        }

        return $this->buildListResponse();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Model  $billingInfo
     * @return \Illuminate\Http\Response
     */
    public function destroy(Model $billingInfo)
    {
        if (Auth::user()->cant('delete', $billingInfo)) {
            abort(403);
        }

        $this->repo->delete($billingInfo);

        return $this->buildListResponse();
    }
}
