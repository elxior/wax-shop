<?php

namespace Wax\Shop\Payment\Validators\AuthorizeNetCim;

use Omnipay\AuthorizeNet\Message\CIMAbstractResponse;
use Illuminate\Support\MessageBag;
use Wax\Shop\Validators\AbstractValidator;

/**
 * This class is to catch raw payment gateway errors and convert it to a more useful exception
 */
class PaymentProfileResponseParser extends AbstractValidator
{
    protected $response;

    public function __construct(CIMAbstractResponse $response)
    {
        $this->response = $response;
    }

    public function passes() : bool
    {
        $this->messages = new MessageBag;

        if ($this->response->isSuccessful()) {
            return true;
        }

        switch ($this->response->getReasonCode()) {
            case 'E00039':
                $this->errors()->add('number', __('shop::payment.duplicate_stored_card'));
                break;

            default:
                $this->errors()->add(
                    'general',
                    ($this->response->getReasonCode().': '.$this->response->getMessage())
                );
                break;
        }

        return false;
    }
}
