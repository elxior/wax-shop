<?php

namespace App\Shop\Support\Tax;

class Shipping
{

    protected $description;
    protected $amount;

    public function __construct(string $description = null, float $amount = null)
    {
        if (!is_null($description)) {
            $this->setDescription($description);
        }

        if (!is_null($amount)) {
            $this->setAmount($amount);
        }
    }

    /**
     * Set the name/description of the shipping service.
     *
     * @param string $value
     * @return self
     */
    public function setDescription(string $value)
    {
        $this->description = $value;
        return $this;
    }

    /**
     * Get the shipping description.
     *
     * @return string
     */
    public function getDescription() : string
    {
        return $this->description ?? '';
    }

    /**
     * Set the shipping price.
     *
     * @param float $value
     * @return self;
     */
    public function setAmount(float $value)
    {
        $this->amount = $value;
        return $this;
    }

    /**
     * Get the shipping price.
     *
     * @return float
     */
    public function getAmount() : float
    {
        return $this->amount ?? 0;
    }
}
