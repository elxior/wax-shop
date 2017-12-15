<?php

namespace Wax\Shop\Validators;

use Illuminate\Support\MessageBag;
use Wax\Core\Support\Localization\Currency;
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

        if ($this->order->balance_due == 0) {
            $this->errors()->add(
                'general',
                __('shop::cart.validation_balance_due', ['amount' => Currency::format(0)])
            );
        }

        if (!$this->order->validateHasItems()) {
            $this->errors()->add('general', __('shop::cart.validation_empty'));
        }

        if (!$this->order->validateItems()) {
            $this->errors()->add('general', __('shop::cart.validation_inventory'));
        }

        if (!$this->order->validateShipping()) {
            $this->errors()->add('general', __('shop::cart.validation_shipping'));
        }

        if (!$this->order->validateTax()) {
            $this->errors()->add('general', __('shop::cart.validation_tax'));
        }

        return $this->messages->isEmpty();
    }
}
