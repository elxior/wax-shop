<?php

namespace Wax\Shop\Validators;

use Wax\Shop\Facades\ShopServiceFacade;
use Wax\Shop\Models\Order\Item;
use Illuminate\Support\MessageBag;

class DeleteOrderItemValidator extends AbstractValidator
{
    protected $itemId;
    protected $quantity;

    public function __construct(int $itemId)
    {
        $this->itemId = $itemId;
    }

    public function passes() : bool
    {
        $this->messages = new MessageBag;

        $item = ShopServiceFacade::getActiveOrder()->items->where('id', $this->itemId)->first();
        if (!$item) {
            $this->errors()->add('item_id', 'Invalid Order Item');
            return false;
        }

        return $this->messages->isEmpty();
    }
}
