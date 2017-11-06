<?php

namespace Wax\Shop\Validators;

use Omnipay\Common\CreditCard;
use Omnipay\Common\Helper;
use Illuminate\Support\MessageBag;

class CreditCardValidator extends AbstractValidator
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
            $this->errors()->add('', 'Credit card number is invalid.');
        }

        if (empty($this->card->getExpiryMonth()) || empty($this->card->getExpiryYear())) {
            $this->errors()->add('', 'Credit Card expiration date is required.');
        }

        if (empty($this->card->getCvv())) {
            $this->errors()->add('', 'Credit Card security code is required.');
        }

        if (empty($this->card->getFirstName())) {
            $this->errors()->add('', 'First Name is required on billing address.');
        }

        if (empty($this->card->getLastName())) {
            $this->errors()->add('', 'Last Name is required on billing address.');
        }

        if (empty($this->card->getBillingAddress1())) {
            $this->errors()->add('', 'Billing Address is required.');
        }

        if (empty($this->card->getPostcode())) {
            $this->errors()->add('', 'Zip / Postal Code is required on billing address.');
        }

        return $this->messages->isEmpty();
    }
}
