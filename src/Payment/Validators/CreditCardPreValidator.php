<?php

namespace Wax\Shop\Payment\Validators;

use Omnipay\Common\CreditCard;
use Omnipay\Common\Helper;
use Illuminate\Support\MessageBag;
use Wax\Shop\Validators\AbstractValidator;

class CreditCardPreValidator extends AbstractValidator
{
    protected $card;

    public function __construct(CreditCard $card)
    {
        $this->card = $card;
    }

    public function passes() : bool
    {
        $this->messages = new MessageBag;

        if (!strlen($this->card->getNumber()) || !Helper::validateLuhn($this->card->getNumber())) {
            $this->errors()->add('cardNumber', 'Credit card number is invalid.');
        }

        if (empty($this->card->getExpiryMonth()) || empty($this->card->getExpiryYear())) {
            $this->errors()->add('cardNumber', 'Credit Card expiration date is required.');
        }

        if (empty($this->card->getCvv())) {
            $this->errors()->add('cvc', 'Credit Card security code is required.');
        }

        if (empty($this->card->getFirstName())) {
            $this->errors()->add('firstName', 'First Name is required on billing address.');
        }

        if (empty($this->card->getLastName())) {
            $this->errors()->add('lastName', 'Last Name is required on billing address.');
        }

        if (empty($this->card->getBillingAddress1())) {
            $this->errors()->add('address', 'Billing Address is required.');
        }

        if (empty($this->card->getPostcode())) {
            $this->errors()->add('zip', 'Zip / Postal Code is required on billing address.');
        }

        return $this->messages->isEmpty();
    }
}
