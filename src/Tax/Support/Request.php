<?php

namespace Wax\Shop\Tax\Support;

use Exception;
use Illuminate\Support\Collection;

class Request
{
    protected $requestId;
    protected $customerId;
    protected $exemptionNumber;

    /* @var Address */
    protected $address;

    /* @var Shipping */
    protected $shipping;

    /* @var Collection */
    protected $lineItems;

    public function __construct()
    {
        $this->lineItems = collect();
    }

    /**
     * Set a unique id for the order.
     *
     * @param string $requestId Order invoice number or document tracking code.
     * @return self
     */
    public function setRequestId(string $requestId)
    {
        $this->requestId = $requestId;
        return $this;
    }

    /**
     * Set a customer id for the order.
     *
     * @param string $customerId User id or identifying customer code.
     * @return self
     */
    public function setCustomerId(string $customerId)
    {
        $this->customerId = $customerId;
        return $this;
    }

    /**
     * Set an exemption number for the order.
     *
     * @param string $exemptionNumber customer's tax exempt number
     * @return self
     */
    public function setExemptionNumber(string $exemptionNumber)
    {
        $this->exemptionNumber = $exemptionNumber;
        return $this;
    }

    /**
     * Set the destination address for the shipment.
     * @param Address $address
     * @return self
     */
    public function setAddress(Address $address)
    {
        $this->address = $address;
        return $this;
    }

    /**
     * Set the shipping amount on the order.
     *
     * @param Shipping $shipping
     * @return self
     */
    public function setShipping(Shipping $shipping)
    {
        $this->shipping = $shipping;
        return $this;
    }

    /**
     * Add a product to the transaction.
     *
     * @param LineItem $lineItem
     * @return self
     */
    public function addLineItem(LineItem $lineItem)
    {
        $this->lineItems->push($lineItem);
        return $this;
    }

    /**
     * Get the unique id of the order.
     */
    public function getRequestId() : ?string
    {
        return $this->requestId;
    }

    /**
     * Get the customer id for the order.
     *
     * @return null|string
     */
    public function getCustomerId() : string
    {
        return $this->customerId ?? '0';
    }

    /**
     * Get the exemption number for the order.
     *
     * @return null|string
     */
    public function getExemptionNumber() : string
    {
        return $this->exemptionNumber ?? '';
    }

    /**
     * Get the shipping address.
     *
     * @return Address
     * @throws Exception
     */
    public function getAddress() : Address
    {
        if (is_null($this->address)) {
            throw new Exception('Address is missing for tax request');
        }
        return $this->address;
    }

    /**
     * Get the shipping rate.
     *
     * @return Shipping|null
     */
    public function getShipping() : ?Shipping
    {
        return $this->shipping;
    }

    /**
     * Get the shipment cart items.
     *
     * @throws Exception;
     * @return Collection|LineItem[]
     */
    public function getLineItems() : Collection
    {
        if ($this->lineItems->isEmpty()) {
            throw new Exception('No line items have been added to the tax request');
        }
        return $this->lineItems;
    }
}
