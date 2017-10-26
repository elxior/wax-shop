<?php

namespace Wax\Shop\Filters;

use Wax\Core\Filters\FilterAggregator;

class CatalogFilterAggregator extends FilterAggregator
{
    protected $defaultAllLabel = 'All';

    public function __construct(CategoryFilter $categoryFilter)
    {
        parent::__construct($categoryFilter);
    }
}
