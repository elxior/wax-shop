<?php

namespace Tests\Shop\Support;

use Illuminate\Foundation\Bootstrap\RegisterProviders;
use Tests\WaxAppTestCase;
use Wax\Core\Support\ConfigurationDatabase;
use Wax\Pages\Providers\PagesServiceProvider;
use Wax\Shop\Providers\ShopServiceProvider;

class ShopBaseTestCase extends WaxAppTestCase
{
    protected $testMailFrom = 'noreply@example.org';
    protected $testMailTo = 'test1@example.org, test2@example.org';

    public function setUp()
    {
        // Make sure ShopServiceProvider is before PagesServiceProvider in the list BEFORE the providers boot
        $this->registerBeforeBootstrappingCallback(RegisterProviders::class, function () {
            $providers = config('app.providers');

            if (false !== ($offset = array_search(PagesServiceProvider::class, $providers))) {
                array_splice($providers, $offset, 0, ShopServiceProvider::class);
            } else {
                $providers[] = ShopServiceProvider::class;
            }

            config(['app.providers' => $providers]);
        });

        parent::setUp();

        $migrator = $this->app->make('migrator');
        $migrator->run(__DIR__ . '/../../database/migrations');

        app()->bind(ConfigurationDatabase::class, function () {
            $double = \Mockery::mock(ConfigurationDatabase::class);
            $double->shouldReceive('get')
                ->with('WEBSITE_MAILFROM')
                ->andReturn($this->testMailFrom);

            $double->shouldReceive('get')
                ->with('WEBSITE_MAILTO')
                ->andReturn($this->testMailTo);

            return $double;
        });
    }
}
