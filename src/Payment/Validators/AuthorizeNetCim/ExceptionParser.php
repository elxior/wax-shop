<?php

namespace Wax\Shop\Payment\Validators\AuthorizeNetCim;

use Illuminate\Support\MessageBag;
use Omnipay\Common\Exception\InvalidCreditCardException;
use Wax\Shop\Validators\AbstractValidator;

/**
 * This class is to catch raw payment gateway errors and convert it to a more useful exception
 */
class ExceptionParser extends AbstractValidator
{
    protected $exception;

    public function __construct(\Exception $exception)
    {
        $this->exception = $exception;
    }

    public function passes() : bool
    {
        $this->messages = new MessageBag;

        if ($this->exception instanceof InvalidCreditCardException) {
            $this->parseCreditCardException();
        } else {
            $this->errors()->add(
                'general',
                __('shop::payment.general_exception', ['message' => $this->exception->getMessage()])
            );
        }

        return $this->messages->isEmpty();
    }

    protected function parseCreditCardException()
    {
        switch ($this->exception->getMessage()) {
            case 'Card has expired':
                $this->errors()->add(
                    'expYear',
                    __('shop::payment.store_payment_exception', ['message' => $this->exception->getMessage()])
                );
                break;

            default:
                $this->errors()->add(
                    'general',
                    __('shop::payment.store_payment_exception', ['message' => $this->exception->getMessage()])
                );
        }
    }
}
