<?php

namespace Wax\Shop\Validators;

use Illuminate\Support\MessageBag;
use Wax\Shop\Models\Order;

class OrderPayableValidator extends AbstractValidator
{
    /** @var  Order $order */
    protected $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function passes() : bool
    {
        $this->messages = new MessageBag;

        if (!$this->order->validateHasItems()) {
            $this->errors()->add('general',  __('shop::cart.validation_empty'));
        }

        if (!$this->order->validateInventory()) {
            $this->errors()->add('general', __('shop::cart.validation_inventory'));
        }

        if (!$this->order->validateShipping()) {
            $this->errors()->add('general', __('shop::cart.validation_shipping'));
        }

        if (!$this->order->validateTax()) {
            $this->errors()->add('general',  __('shop::cart.validation_tax'));
        }

        return $this->messages->isEmpty();
    }
}
