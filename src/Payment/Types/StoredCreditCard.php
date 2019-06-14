<?php

namespace Wax\Shop\Payment\Types;

use Wax\Shop\Models\Order\Payment;
use Wax\Shop\Models\User\PaymentMethod;
use Wax\Shop\Payment\Contracts\PaymentTypeContract;
use Wax\Shop\Payment\Repositories\PaymentMethodRepository;

class StoredCreditCard implements PaymentTypeContract
{

    /* @var \Wax\Shop\Payment\Repositories\PaymentMethodRepository */
    protected $paymentMethodRepo;

    /* @var PaymentMethod */
    protected $paymentMethod;

    public function __construct()
    {
        $this->paymentMethodRepo = app()->make(PaymentMethodRepository::class);
    }

    public function authorize($order, $amount) : Payment
    {
        return $this->paymentMethodRepo->authorizePayment($order, $this->paymentMethod, $amount = null);
    }

    public function purchase($order, $amount) : Payment
    {
        return $this->paymentMethodRepo->makePayment($order, $this->paymentMethod, $amount = null);
    }

    public function capture(Payment $payment)
    {
        return true;
    }

    public function getCardData($data)
    {
        $name = explode(' ', $data['name']);
        $firstname = $name[0];
        $lastname = implode(' ', array_slice($name, 1));

        $data['expiry'] = str_replace(' ', '', $data['expiry']);
        if (strpos($data['expiry'], '/') === false) {
            $data['expiry'] =
                substr($data['expiry'], 0, 2) . '/' .
                substr($data['expiry'], -1 * (strlen($data['expiry']) - 2));
        }
        $expDate = array_map(function ($n) use ($data) {
            return preg_replace("/[^0-9]/", "", substr('00' . $n, -2));
        }, explode('/', $data['expiry']));

        return [
            'number' => str_replace(' ', '', $data['number']),
            'expiryMonth' => $expDate[0],
            'expiryYear' => $expDate[1],
            'cvv' => $data['cvc'],
            'firstName' => $firstname,
            'lastName' => $lastname,
            'billingAddress1' => $data['billing-address'],
            'billingPostcode' => $data['postal-code'],
        ];
    }

    public function loadData($data)
    {
        if (empty($data['id'])) {
            if (!empty($data['payment-method']) && $data['payment-method'] == 'replace') {
                $current = $this->paymentMethodRepo->getAll()->first();
                if (!is_null($current)) {
                    $this->paymentMethodRepo->delete($current);
                }
            }

            $this->paymentMethod = $this->paymentMethodRepo->create($this->getCardData($data));
        } else {
            $this->paymentMethod = PaymentMethod::find($data['id']);
        }
    }
}
