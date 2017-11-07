<?php

namespace Wax\Shop\Tax\Support;

class LineItem
{

    protected $itemCode;
    protected $unitPrice;
    protected $quantity = 1;
    protected $taxable = true;

    public function __construct(
        string $itemCode = null,
        float $unitPrice = null,
        int $quantity = null,
        bool $taxable = null
    ) {
        if (!is_null($itemCode)) {
            $this->setItemCode($itemCode);
        }

        if (!is_null($unitPrice)) {
            $this->setUnitPrice($unitPrice);
        }

        if (!is_null($quantity)) {
            $this->setQuantity($quantity);
        }

        if (!is_null($taxable)) {
            $this->setTaxable($taxable);
        }
    }


    /**
     * Set the item code (SKU or Product ID).
     *
     * @param string $value Sku or product ID.
     * @return self
     */
    public function setItemCode(string $value)
    {
        $this->itemCode = $value;
        return $this;
    }

    /**
     * Get the item code.
     *
     * @return string
     */
    public function getItemCode() : string
    {
        return $this->itemCode;
    }

    /**
     * Set the unit price.
     *
     * @param float $value Sale price (after discount, per unit).
     * @return self
     */
    public function setUnitPrice(float $value)
    {
        $this->unitPrice = $value;
        return $this;
    }

    /**
     * Get the unit price.
     *
     * @return float
     */
    public function getUnitPrice() : float
    {
        return $this->unitPrice;
    }

    /**
     * Set the quantity.
     *
     * @param int $value Units sold in this line item.
     * @return self
     */
    public function setQuantity(int $value)
    {
        $this->quantity = $value;
        return $this;
    }

    /**
     * Get the quantity.
     *
     * @return int
     */
    public function getQuantity() : int
    {
        return $this->quantity;
    }

    /**
     * Set the item taxability flag
     *
     * @param bool $taxable
     * @return self
     */
    public function setTaxable(bool $taxable)
    {
        $this->taxable = $taxable;
        return $this;
    }

    /**
     * Get the item taxability flag.
     *
     * @return bool
     */
    public function getTaxable() : bool
    {
        return $this->taxable;
    }
}
