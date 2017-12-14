<?php

namespace Wax\Shop\Validators;

use Wax\Shop\Facades\ShopServiceFacade;
use Wax\Shop\Models\Order\Item;
use Wax\Shop\Models\Product;
use Wax\Shop\Models\Product\OptionModifier;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\MessageBag;
use Wax\Shop\Repositories\ProductRepository;

class OrderItemValidator extends AbstractValidator
{
    protected $itemId;
    protected $productId;
    protected $quantity;
    protected $options;
    protected $customizations;

    /**
     * we're adding somethint to the cart from the request. Set it up!
     *
     * @param int   $productId
     * @param int   $quantity
     * @param array $options        Should be like [[optionId => optionValueId], [optionId => optionValueId]]
     * @param array $customizations
     */
    public function setRequest(int $productId, int $quantity, array $options, array $customizations)
    {
        $this->productId = $productId;
        $this->quantity = $quantity;
        $this->options = collect($options);
        $this->customizations = $customizations;

        return $this;
    }

    public function setItemId(int $itemId)
    {
        $this->itemId = $itemId;

        return $this;
    }

    public function setQuantity(int $quantity)
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function passes() : bool
    {
        $this->messages = new MessageBag;

        if ($this->itemId !== null) {
            $item = Item::find($this->itemId);
            
            if (is_null($item)) {
                $this->errors()->add('item_id', 'Invalid Cart Item');
                return false;
            }

            $options = $item->options->mapWithKeys(function ($option) {
                return [$option->id => $option->value_id];
            })->toArray();

            $this->setRequest($item->product_id, $this->quantity ?? $item->quantity, $options, $item->customizations->toArray());
        }

        $product = app()->make(ProductRepository::class)->get($this->productId);

        if (is_null($product)) {
            $this->errors()->add('product_id', 'Invalid Product');
            return false;
        }

        $this->checkOnePerUser($product)
        && $this->checkOptionsAgainstProduct($product)
        && $this->checkRequiredOptions($product)
        && $this->checkInventory($product);

        return $this->messages->isEmpty();
    }

    /**
     * Check "one-per-user" products for validity in the current cart.
     *
     * @param Product $product
     * @return bool
     */
    protected function checkOnePerUser(Product $product) : bool
    {
        if (!$product->one_per_user) {
            return true;
        }

        if ($this->quantity > 1) {
            $this->errors()->add('quantity', 'You cannot purchase more than one of this product.');
            return false;
        }

        $orderHasProduct = ShopServiceFacade::orderHasProduct($product->id, $this->options->toArray(), null);
        if ($orderHasProduct) {
            $this->errors()->add('product_id', 'This product is already in your cart.');
            return false;
        }

        $userOwnsProduct = ShopServiceFacade::userOwnsProduct($product->id, $this->options->toArray(), null);
        if ($userOwnsProduct) {
            $this->errors()->add('product_id', 'You have already purchased this product.');
            return false;
        }

        return true;
    }

    /**
     * Make sure all required options for the given product have valid values in the user input.
     *
     * @param Product $product
     * @return bool
     */
    protected function checkRequiredOptions(Product $product) : bool
    {
        $missingOptions = $product->options
            ->where('pivot.required', 1)
            ->whereNotIn('id', $this->options->filter()->keys());

        if ($missingOptions->isNotEmpty()) {
            $this->errors()
                ->add('option', 'Missing required options: '.$missingOptions->pluck('name')->implode(', '));
            return false;
        }
        return true;
    }

    /**
     * Check inventory availability for the requested Product & Options
     *
     * @param Product $product
     * @return bool
     */
    protected function checkInventory(Product $product) : bool
    {
        $modifier = $this->getProductModifierForRequest($product);

        if (!is_null($modifier)) {
            return $this->checkProductModifierInventory($modifier);
        }

        return $this->checkBaseProductInventory($product);
    }

    /**
     * Check inventory / availability of a Product/OptionModifier.
     *
     * @param OptionModifier $modifier
     * @return bool
     */
    protected function checkProductModifierInventory(OptionModifier $modifier)
    {
        if ($modifier->disable) {
            $this->errors()->add('option', 'This item is currently unavailable.');
            return false;
        }

        // How many are already in the user's cart?
        $pendingQuantity = ShopServiceFacade::getActiveOrder()
            ->items
            ->filter(function ($item) use ($modifier) {
                return !is_null($item->modifier) && $item->modifier->is($modifier);
            })
            ->sum('quantity');

        $effectiveInventory = $modifier->effective_inventory - $pendingQuantity;

        if ($effectiveInventory < $this->quantity) {
            $this->errors()
                ->add('quantity', 'There is insufficient inventory available to fulfill your request.');
            return false;
        }

        return true;
    }

    /**
     * Check inventory / availability of a base Product
     *
     * @param Product $product
     * @return bool
     */
    protected function checkBaseProductInventory(Product $product)
    {
        // How many are already in the user's cart?
        $pendingQuantity = ShopServiceFacade::getActiveOrder()
            ->items
            ->where('product_id', $product->id)
            ->sum('quantity');

        $effectiveInventory = $product->effective_inventory - $pendingQuantity;

        if ($effectiveInventory < $this->quantity) {
            $this->errors()
                ->add('quantity', 'There is insufficient inventory available to fulfill your request.');
            return false;
        }

        return true;
    }

    /**
     * Get the product modifier matching the requested options, if one exists.
     *
     * @param Product $product
     * @return OptionModifier|null
     */
    protected function getProductModifierForRequest(Product $product) : ?OptionModifier
    {
        if ($this->options->isEmpty()) {
            return null;
        }

        return $product->optionModifiers
            ->where('values', $this->options->sort()->implode('-'))
            ->first();
    }

    /**
     * Filter the input for option/value id pairs that are valid for the given product.
     *
     * @param Product $product
     * @return Collection
     */
    protected function checkOptionsAgainstProduct(Product $product) : bool
    {
        if ($this->options->isEmpty()) {
            return true;
        }

        $validOptions = $this->options
            ->filter(function ($optionValueId, $optionId) use ($product) {
                return $product->options->filter(function ($option) use ($optionId, $optionValueId) {
                    return $option->id == $optionId
                        && $option->values->pluck('id')->contains($optionValueId);
                });
            })
            ->isNotEmpty();

        if (!$validOptions) {
            $this->errors()
                ->add('options', 'Those options are not valid for this product.');
            return false;
        }

        return true;
    }
}
