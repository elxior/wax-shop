<?php

namespace Wax\Shop\Tax\Contracts;

use Wax\Shop\Tax\Support\Request;
use Wax\Shop\Tax\Support\Response;

interface TaxDriverContract
{
    /**
     * Execute a tax rate lookup.
     *
     * @throws \Exception Address validation
     * @param Request $request
     * @return Response
     */
    public function getTax(Request $request) : Response;

    /**
     * Mark a transaction as complete for tax accounting purposes.
     *
     * @param Request $request
     * @return bool
     */
    public function commit(Request $request) : bool;
}
