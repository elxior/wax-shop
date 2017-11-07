<?php

namespace Wax\Shop\Tax\Drivers;

use Wax\Shop\Models\Tax;
use Wax\Shop\Tax\Contracts\TaxDriverContract;
use Wax\Shop\Tax\Support\LineItem;
use Wax\Shop\Tax\Support\Request;
use Wax\Shop\Tax\Support\Response;

class DbDriver implements TaxDriverContract
{

    /**
     * Execute a tax rate lookup.
     *
     * @param Request $request
     * @return Response
     */
    public function getTax(Request $request): Response
    {
        $tax = Tax::where('zone', $request->getAddress()->getRegion())->first();
        if (!$tax) {
            return new Response();
        }

        $amount = $request->getLineItems()->filter->getTaxable()->reduce(function ($carry, $item) use ($tax) {
            /* @var LineItem $item */
            return $carry + ($item->getUnitPrice() * $item->getQuantity())
                * ($tax->rate / 100);
        }, 0);

        if ($tax->tax_shipping) {
            $amount += $request->getShipping()->getAmount() * ($tax->rate / 100);
        }

        return (new Response)
            ->setDescription("{$tax->zone} {$tax->rate}%")
            ->setRate($tax->rate)
            ->setTaxShipping($tax->tax_shipping)
            ->setAmount($amount);
    }

    /**
     * Mark a transaction as complete for tax accounting purposes.
     *
     * @param Request $request
     * @return bool
     */
    public function commit(Request $request): bool
    {
        // Nothing to do for this driver
        return true;
    }
}
