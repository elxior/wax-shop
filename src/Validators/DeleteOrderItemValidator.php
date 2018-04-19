<?php

namespace Wax\Shop\Validators;

use Wax\Shop\Facades\ShopServiceFacade;
use Wax\Shop\Models\Order;
use Wax\Shop\Models\Order\Item;
use Illuminate\Support\MessageBag;

class DeleteOrderItemValidator extends AbstractValidator
{
    protected $itemId;
    protected $order;

    public function __construct(int $itemId, Order $order)
    {
        $this->itemId = $itemId;
        $this->order = $order;
    }

    public function passes() : bool
    {
        $this->messages = new MessageBag;

        $item = $this->order->items->where('id', $this->itemId)->first();
        if (!$item) {
            $this->errors()->add('item_id', 'Invalid Order Item');
            return false;
        }

        return $this->messages->isEmpty();
    }
}
