<?php

namespace Tests\Shop;

use Tests\WaxAppTestCase;
use Wax\Shop\Providers\ShopServiceProvider;

class ShopBaseTestCase extends WaxAppTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->app->register(ShopServiceProvider::class);

        \Route::getRoutes()->refreshNameLookups();

        $migrator = $this->app->make('migrator');
        $migrator->run(__DIR__.'/../database/migrations');

        // it would be nice if wax-app ran these automatically.
        $migrator->run(__DIR__.'/../vendor/oohology/wax-cms/modules/core/database/migrations');
        //$migrator->run(__DIR__.'/../vendor/oohology/wax-cms/modules/pages/database/migrations');
    }
}
