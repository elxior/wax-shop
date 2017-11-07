<?php

namespace Wax\Shop\Tax\Support;

class Address
{

    protected $line1;
    protected $line2;
    protected $line3;
    protected $city;
    protected $region;
    protected $postalCode;
    protected $country;

    /**
     * You can set the full address while instantiating.
     *
     * @param null|string $line1 The street address, attention line, or business name of the location.
     * @param null|string $line2 The street address, business name, or apartment/unit number of the location.
     * @param null|string $line3 Additional street address, business name, or apartment/unit number of the location.
     * @param null|string $city City of the location.
     * @param null|string $region State or Region of the location.
     * @param null|string $postalCode Postal/zip code of the location.
     * @param null|string $country The two-letter country code of the location.
     */
    public function __construct(
        string $line1 = null,
        string $line2 = null,
        string $line3 = null,
        string $city = null,
        string $region = null,
        string $postalCode = null,
        string $country = null
    ) {
        $this->setAddress($line1, $line2, $line3, $city, $region, $postalCode, $country);
    }

    /**
     * Set address line 1.
     *
     * @param string $line1
     * @return self
     */
    public function setLine1(string $line1)
    {
        $this->line1 = $line1;
        return $this;
    }

    /**
     * Set address line 2.
     *
     * @param string $line2
     * @return self
     */
    public function setLine2(string $line2)
    {
        $this->line2 = $line2;
        return $this;
    }

    /**
     * Set address line 3.
     *
     * @param string $line3
     * @return self
     */
    public function setLine3(string $line3)
    {
        $this->line3 = $line3;
        return $this;
    }

    /**
     * Set the city;
     *
     * @param string $city
     * @return self
     */
    public function setCity(string $city)
    {
        $this->city = $city;
        return $this;
    }

    /**
     * Set the region, e.g. state or province.
     *
     * @param string $state
     * @return self
     */
    public function setRegion(string $state)
    {
        $this->region = $state;
        return $this;
    }

    /**
     * Set the zip / postal code.
     *
     * @param string $postalCode
     * @return self
     */
    public function setPostalCode(string $postalCode)
    {
        $this->postalCode = $postalCode;
        return $this;
    }

    /**
     * Set the two-letter country code.
     *
     * @param string $country
     * @throws \Exception;
     * @return self
     */
    public function setCountry(string $country)
    {
        if (!preg_match('/^([a-z]{2})?$/i', $country)) {
            throw new \Exception("Invalid country code");
        }

        $this->country = $country;
        return $this;
    }

    /**
     * Short-hand to set the full address
     *
     * @param null|string $line1 The street address, attention line, or business name of the location.
     * @param null|string $line2 The street address, business name, or apartment/unit number of the location.
     * @param null|string $line3 Additional street address, business name, or apartment/unit number of the location.
     * @param null|string $city City of the location.
     * @param null|string $region State or Region of the location.
     * @param null|string $postalCode Postal/zip code of the location.
     * @param null|string $country The two-letter country code of the location.
     */
    public function setAddress(
        string $line1 = null,
        string $line2 = null,
        string $line3 = null,
        string $city = null,
        string $region = null,
        string $postalCode = null,
        string $country = null
    ) {
        if (!is_null($line1)) {
            $this->setLine1($line1);
        }
        if (!is_null($line2)) {
            $this->setLine2($line2);
        }
        if (!is_null($line3)) {
            $this->setLine3($line3);
        }
        if (!is_null($city)) {
            $this->setCity($city);
        }
        if (!is_null($region)) {
            $this->setRegion($region);
        }
        if (!is_null($postalCode)) {
            $this->setPostalCode($postalCode);
        }
        if (!is_null($country)) {
            $this->setCountry($country);
        }
    }

    /**
     * Getter for address $line1.
     *
     * @return string
     */
    public function getLine1() : ?string
    {
        return $this->line1;
    }

    /**
     * Getter for address $line2.
     *
     * @return string
     */
    public function getLine2() : ?string
    {
        return $this->line2;
    }

    /**
     * Getter for address $line3.
     *
     * @return string
     */
    public function getLine3() : ?string
    {
        return $this->line3;
    }

    /**
     * Getter for $city.
     *
     * @return string
     */
    public function getCity() : ?string
    {
        return $this->city;
    }

    /**
     * Getter for region.
     *
     * @return string
     */
    public function getRegion() : ?string
    {
        return $this->region;
    }

    /**
     * Getter for postal code.
     *
     * @return string
     */
    public function getPostalCode() : ?string
    {
        return $this->postalCode;
    }

    /**
     * Getter for country.
     *
     * @return string
     */
    public function getCountry() : ?string
    {
        return $this->country;
    }
}
