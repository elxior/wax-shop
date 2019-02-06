<?php

namespace Wax\Shop\Tax\Drivers;

use Avalara\AvaTaxClient;
use Avalara\DocumentType;
use Avalara\TransactionBuilder;
use Wax\Shop\Tax\Contracts\TaxDriverContract;
use Wax\Shop\Tax\Exceptions\AddressException;
use Wax\Shop\Tax\Exceptions\ApiException;
use Wax\Shop\Tax\Support\LineItem;
use Wax\Shop\Tax\Support\Request;
use Wax\Shop\Tax\Support\Response;

/**
 * Tax driver for the AvaTax service by Avalara. Requires the AvaTax SDK, available via Composer.
 *
 * `composer require avalara/avataxclient`
 *
 * @link https://github.com/avadev/AvaTax-REST-V2-PHP-SDK AvaTax SDK on GitHub
 * @link https://developer.avalara.com/ Avalara developer site
 */
class AvalaraDriver implements TaxDriverContract
{
    protected $client;

    public function __construct(AvaTaxClient $client)
    {
        $this->client = $client;
    }

    /**
     * Execute a tax rate lookup.
     *
     * @param Request $request
     * @return Response
     */
    public function getTax(Request $request): Response
    {
        $builder = new TransactionBuilder(
            $this->client,
            config('wax.shop.tax.avalara.company_code'),
            DocumentType::C_SALESORDER,
            $request->getCustomerId()
        );

        if (strlen($request->getExemptionNumber())) {
            $builder->withExemptionNo($request->getExemptionNumber());
        }

        $transaction = $this->buildTransaction($builder, $request);

        $result = $transaction->create();

        if (property_exists($result, 'error')) {
            $this->handleError($result->error->details);
        }

        return $this->buildResponse($result);
    }

    protected function buildTransaction(TransactionBuilder $builder, Request $request)
    {
        $address = $request->getAddress();
        $transaction = $builder->withAddress(
            'ShipTo',
            $address->getLine1(),
            $address->getLine2(),
            $address->getLine3(),
            $address->getCity(),
            $address->getRegion(),
            $address->getPostalCode(),
            $address->getCountry() ?: 'US'
        );

        $request->getLineItems()->each(function ($item) use (&$transaction) {
            /* @var LineItem $item */
            $transaction = $transaction->withLine(
                $item->getUnitPrice() * $item->getQuantity(),
                $item->getQuantity(),
                $item->getItemCode(),
                $item->getTaxable() ? 'P0000000' : 'NT'
            );
        });

        if ($request->getShipping()->getAmount()) {
            $transaction = $transaction->withLine(
                $request->getShipping()->getAmount(),
                1,
                $request->getShipping()->getDescription(),
                'FR'
            );
        }
        return $transaction;
    }

    protected function buildResponse($result)
    {
        $rate = 0;
        $amount = 0;
        $region = null;

        foreach ($result->summary as $summary) {
            if (is_null($region)) {
                $region = $summary->region;
            }
            $rate += $summary->rate;
            $amount += $summary->tax;
        }

        $rate *= 100;
        $amount = round($amount, 2);

        return (new Response)
            ->setDescription("{$region} {$rate}%")
            ->setRate($rate)
            ->setTaxShipping(
                $this->getTaxShipping($result)
            )
            ->setAmount($amount);
    }

    /**
     * Determines from the API response whether tax was applied to shipping.
     *
     * @param $result
     * @return bool
     */
    protected function getTaxShipping($result)
    {
        foreach ($result->lines as $line) {
            if (preg_match('/^FR/', $line->taxCode) && ($line->tax > 0)) {
                return true;
            }
        }
        return false;
    }

    protected function handleError($errors)
    {
        $details = collect($errors)
            ->where('severity', 'Error');

        $addressError = $details->where('refersTo', 'Addresses[0]')->first();
        if ($addressError) {
            throw new AddressException($addressError->description);
        }


        throw new ApiException($details->implode('description', '. '));
    }

    /**
     * Mark a transaction as complete for tax accounting purposes.
     *
     * @param Request $request
     * @return bool
     */
    public function commit(Request $request): bool
    {
        $builder = new TransactionBuilder(
            $this->client,
            config('wax.shop.tax.avalara.company_code'),
            DocumentType::C_SALESINVOICE,
            $request->getCustomerId()
        );

        if (strlen($request->getExemptionNumber())) {
            $builder->withExemptionNo($request->getExemptionNumber());
        }

        $transaction = $this->buildTransaction($builder, $request)
            ->withCommit();

        $result = $transaction->create();

        if (property_exists($result, 'error')) {
            return false;
        }

        return ($result->status === 'Committed');
    }
}
