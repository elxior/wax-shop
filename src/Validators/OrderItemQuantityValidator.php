<?php

namespace Wax\Shop\Validators;

use Wax\Shop\Facades\ShopServiceFacade;
use Wax\Shop\Models\Order\Item;
use Illuminate\Support\MessageBag;

class OrderItemQuantityValidator extends AbstractValidator
{
    protected $itemId;
    protected $quantity;

    public function __construct(int $itemId, int $quantity)
    {
        $this->itemId = $itemId;
        $this->quantity = $quantity;
    }

    public function passes() : bool
    {
        $this->messages = new MessageBag;

        $item = ShopServiceFacade::getActiveOrder()->items->where('id', $this->itemId)->first();
        if (!$item) {
            $this->errors()->add('item_id', 'Invalid Order Item');
            return false;
        }

        $this->checkOnePerUser($item)
        && $this->checkInventory($item);

        return $this->messages->isEmpty();
    }

    /**
     * Check "one-per-user" products for validity in the current cart.
     *
     * @param Item $item
     * @return bool
     */
    protected function checkOnePerUser(Item $item) : bool
    {
        if (!$item->product->one_per_user) {
            return true;
        }

        if ($this->quantity > 1) {
            $this->errors()->add('quantity', 'You cannot purchase more than one of this product.');
            return false;
        }

        return true;
    }

    /**
     * Check inventory availability for the requested Item
     *
     * @param Item $item
     * @return bool
     */
    protected function checkInventory(Item $item) : bool
    {
        // Find the same item in other shipments
        $pendingQuantity = ShopServiceFacade::getActiveOrder()
            ->items
            ->filter(function ($orderItem) use ($item) {
                if ($orderItem->is($item) || ($orderItem->product_id != $item->product_id)) {
                    return false;
                }

                $itemOptions = $item->options->mapWithKeys(function ($option) {
                    return [$option->option_id => $option->value_id];
                })->toArray();

                $orderItemOptions = $orderItem->options->mapWithKeys(function ($option) {
                    return [$option->option_id => $option->value_id];
                })->toArray();

                return $itemOptions == $orderItemOptions;
            })
            ->sum('quantity');

        $effectiveInventory = $item->inventory - $pendingQuantity;

        if ($effectiveInventory < $this->quantity) {
            $this->errors()
                ->add('quantity', 'There is insufficient inventory available to fulfill your request.');
            return false;
        }

        return true;
    }
}
