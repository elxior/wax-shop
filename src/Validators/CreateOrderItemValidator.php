<?php

namespace Wax\Shop\Validators;

use Wax\Shop\Facades\ShopServiceFacade;
use Wax\Shop\Models\Product;
use Wax\Shop\Models\Product\OptionModifier;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\MessageBag;
use Wax\Shop\Repositories\ProductRepository;

class CreateOrderItemValidator extends AbstractValidator
{
    protected $productId;
    protected $quantity;
    protected $options;
    protected $customizations;

    public function __construct(int $productId, int $quantity, array $options, array $customizations)
    {
        $this->productId = $productId;
        $this->quantity = $quantity;
        $this->options = $options;
        $this->customizations = $customizations;
    }

    public function passes() : bool
    {
        $this->messages = new MessageBag;

        $product = app()->make(ProductRepository::class)->get($this->productId);

        if (is_null($product)) {
            $this->errors()->add('product_id', 'Invalid Product');
            return false;
        }

        $this->checkOnePerUser($product)
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

        $options = $this->getOptionsFromRequest($product)->toArray();

        $orderHasProduct = ShopServiceFacade::orderHasProduct($product->id, $options, null);
        if ($orderHasProduct) {
            $this->errors()->add('product_id', 'This product is already in your cart.');
            return false;
        }

        $userOwnsProduct = ShopServiceFacade::userOwnsProduct($product->id, $options, null);
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
            ->whereNotIn('id', $this->getOptionsFromRequest($product)->filter()->keys());

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
        $options = $this->getOptionsFromRequest($product);

        if ($options->isEmpty()) {
            return null;
        }

        return $product->optionModifiers
            ->where('values', $options->sort()->implode('-'))
            ->first();
    }

    /**
     * Filter the input for option/value id pairs that are valid for the given product.
     *
     * @param Product $product
     * @return Collection
     */
    protected function getOptionsFromRequest(Product $product) : Collection
    {
        return $product->options->mapWithKeys(function ($option) {
            return [$option->id => (int)($this->options[$option->id] ?? 0)];
        });
    }
}
