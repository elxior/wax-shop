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
    protected $item;
    protected $productId;
    protected $product;
    protected $quantity;
    protected $options;
    protected $customizations;

    public function __construct(MessageBag $messages)
    {
        $this->messages = $messages;
    }

    /**
     * We're adding something to the cart from the request. Set it up!
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
        $this->customizations = collect($customizations);

        $this->product = app()->make(ProductRepository::class)->get($this->productId);
        if (is_null($this->product)) {
            $this->errors()->add('product_id', 'Invalid Product');
        }

        return $this;
    }

    public function setItemId(int $itemId)
    {
        $this->itemId = $itemId;
        
        $this->item = Item::find($this->itemId);
            
        if (is_null($this->item)) {
            $this->errors()->add('item_id', 'Invalid Cart Item');
            return $this;
        }
        
        $options = $this->item->options->mapWithKeys(function ($option) {
            return [$option->option_id => $option->value_id];
        })->toArray();

        $customizations = $this->item->customizations->mapWithKeys(function ($customization) {
            return [$customization->customization_id => $customization->value];
        })->toArray();

        $this->setRequest(
            $this->item->product_id,
            $this->quantity ?? $this->item->quantity,
            $options,
            $customizations
        );

        return $this;
    }

    public function setQuantity(int $quantity)
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function passes() : bool
    {
        if (!$this->messages->isEmpty()) {
            return false;
        }

        if (!$this->product) {
            throw new \Exception(
                'Improper usage of OrderItemValidator. You must call `setItem()` or `setRequest()` to initialize.'
            );
        }

        $this->checkOnePerUser($this->product)
        && $this->checkOptionsAgainstProduct($this->product)
        && $this->checkRequiredOptions($this->product)
        && $this->checkCustomizationsAgainstProduct($this->product)
        && $this->checkRequiredCustomizations($this->product)
        && $this->checkInventory($this->product);

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

        // don't validate a cart item against itself unless we're upping it's quantity.
        if (!isset($this->item) || ($this->quantity > $this->item->quantity)) {
            $orderHasProduct = ShopServiceFacade::orderHasProduct($product->id, $this->options->toArray(), null);
            if ($orderHasProduct) {
                $this->errors()->add('product_id', 'This product is already in your cart.');
                return false;
            }
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
     * Make sure all required customization for the given product have values in the user input.
     *
     * @param Product $product
     * @return bool
     */
    protected function checkRequiredCustomizations(Product $product) : bool
    {
        $missingCustomizations = $product->customizations
            ->where('required', 1)
            ->whereNotIn('id', $this->customizations->filter()->keys());

        if ($missingCustomizations->isNotEmpty()) {
            $this->errors()
                ->add(
                    'customization',
                    'Missing required customization(s): '.$missingCustomizations->pluck('name')->implode(', ')
                );
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

        if ($this->itemId) {
            // this is already in the cart, don't double the pending quantity
            $pendingQuantity = $this->quantity - $this->item->quantity;
        } else {
            // How many are already in the user's cart?
            $pendingQuantity = ShopServiceFacade::getActiveOrder()
                ->items
                ->filter(function ($item) use ($modifier) {
                    return !is_null($item->modifier) && $item->modifier->is($modifier);
                })
                ->sum('quantity');
        }

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
        if ($this->itemId) {
            // this is already in the cart, don't double the pending quantity
            $pendingQuantity = $this->quantity - $this->item->quantity;
        } else {
            // How many are already in the user's cart?
            $pendingQuantity = ShopServiceFacade::getActiveOrder()
                ->items
                ->where('product_id', $product->id)
                ->sum('quantity');
        }

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

        $invalidOptions = $this->options
            ->reject(function ($optionValueId, $optionId) use ($product) {
                // reject all the valid option/option-value pairs.
                $option = $product->options->where('id', $optionId)->first();

                if (!$option) {
                    return false;
                }

                return $option->values->pluck('id')->contains($optionValueId);
            })
            ->isNotEmpty();

        if ($invalidOptions) {
            $this->errors()
                ->add('options', 'Those options are not valid for this product.');
            return false;
        }

        return true;
    }

    /**
     * Filter the input for customization values that are valid for the given product.
     *
     * @param Product $product
     * @return boolean
     */
    protected function checkCustomizationsAgainstProduct(Product $product) : bool
    {
        if ($this->customizations->isEmpty()) {
            return true;
        }

        $invalidCustomizations = $this->customizations
            ->reject(function ($value, $customizationId) use ($product) {
                $productCustomization = $product->customizations->where('id', $customizationId)->first();

                if (!$productCustomization) {
                    return false;
                }

                if ($productCustomization->type == 'number') {
                    if (!empty($productCustomization->min)) {
                        return $value > $productCustomization->min;
                    }

                    if (!empty($productCustomization->max)) {
                        return $value < $productCustomization->max;
                    }
                }

                if ($productCustomization->type == 'text' || $productCustomization->type == 'textarea') {
                    if (!empty($productCustomization->min)) {
                        return strlen($value) > $productCustomization->min;
                    }

                    if (!empty($productCustomization->max)) {
                        return strlen($value) < $productCustomization->max;
                    }
                }

                // apparently it's valid
                return true;
            })
            ->isNotEmpty();

        if ($invalidCustomizations) {
            $this->errors()
                ->add('customizations', 'Those customizations are not valid for this product.');
            return false;
        }

        return true;
    }
}
