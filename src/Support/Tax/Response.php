<?php

namespace App\Shop\Support\Tax;

class Response
{
    protected $description;
    protected $rate;
    protected $amount;
    protected $taxShipping;

    /**
     * Set the jurisdiction or tax summary message.
     *
     * @param string $description
     * @return self
     */
    public function setDescription(string $description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * Set the gross tax rate.
     *
     * @param float $rate
     * @return self
     */
    public function setRate(float $rate)
    {
        $this->rate = $rate;
        return $this;
    }

    /**
     * Set the total tax amount.
     *
     * @param float $amount
     * @return self
     */
    public function setAmount(float $amount)
    {
        $this->amount = $amount;
        return $this;
    }

    /**
     * Is shipping taxed for this shipment?
     *
     * @param bool $taxShipping
     * @return self
     */
    public function setTaxShipping(bool $taxShipping)
    {
        $this->taxShipping = $taxShipping;
        return $this;
    }

    /**
     * Get the jurisdiction or tax summary message.
     * @return string
     */
    public function getDescription() : string
    {
        return $this->description ?? '';
    }

    /**
     * Get the gross tax rate.
     * @return float
     */
    public function getRate() : float
    {
        return $this->rate ?? 0;
    }

    /**
     * Get the total tax amount.
     * @return float
     */
    public function getAmount() : float
    {
        return $this->amount ?? 0;
    }

    /**
     * Is shipping taxed for this shipment?
     *
     * @return bool|null
     */
    public function getTaxShipping() : bool
    {
        return $this->taxShipping ?? false;
    }
}
