<?php

namespace Wax\Shop\Facades;

use Wax\Shop\Services\ShopService;
use Illuminate\Support\Facades\Facade;

/**
 * @mixin ShopService
 */
class ShopServiceFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'shop.service';
    }
}
