<?php

namespace Wax\Shop\Validators;

use Illuminate\Support\MessageBag;
use Wax\Core\Support\Localization\Currency;
use Wax\Shop\Models\Order;

class OrderProcessableValidator extends AbstractValidator
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

        if (get_class($this->order) != config('wax.shop.models.order')) {
            $this->errors()->add('general', __('shop::order.validation_incompatible_class'));
        }
        if (is_null($this->order->placed_at)) {
            $this->errors()->add('general', __('shop::order.validation_not_placed'));
        }

        return $this->messages->isEmpty();
    }
}
