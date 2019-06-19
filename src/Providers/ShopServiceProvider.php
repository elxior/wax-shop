<?php

namespace Wax\Shop\Providers;

use Avalara\AvaTaxClient;
use Illuminate\Auth\Events\Login;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factory as EloquentFactory;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Wax\Core\Contracts\FilterAggregatorContract;
use Wax\Core\Contracts\FilterableRepositoryContract;
use Wax\Core\Events\SessionMigrationEvent;
use Wax\Admin\Cms\Cms;
use Wax\Shop\Console\Commands\Install;
use Wax\Shop\Contracts\OrderChangedEventContract;
use Wax\Shop\Events\OrderChanged\CartContentsChangedEvent;
use Wax\Shop\Events\OrderChanged\ShippingAddressChangedEvent;
use Wax\Shop\Events\OrderChanged\ShippingServiceChangedEvent;
use Wax\Shop\Events\OrderPlacedEvent;
use Wax\Shop\Filters\CatalogFilterAggregator;
use Wax\Shop\Http\Controllers\CatalogController;
use Wax\Shop\Listeners\InvalidateOrderShippingListener;
use Wax\Shop\Listeners\InvalidateOrderTaxListener;
use Wax\Shop\Listeners\LoginListener;
use Wax\Shop\Listeners\RecalculateDiscountsListener;
use Wax\Shop\Listeners\SessionMigrationListener;
use Wax\Shop\Models\Order\Bundle;
use Wax\Shop\Models\Order\Item;
use Wax\Shop\Models\User\PaymentMethod;
use Wax\Shop\Observers\OrderBundleObserver;
use Wax\Shop\Observers\OrderItemObserver;
use Wax\Shop\Policies\PaymentMethodPolicy;
use Wax\Shop\Repositories\ProductRepository;
use Wax\Shop\Services\ShopService;
use Wax\Shop\ShopIndexer;
use Wax\Shop\Tax\Contracts\TaxDriverContract;

class ShopServiceProvider extends ServiceProvider
{
    protected $policies = [
        PaymentMethod::class => PaymentMethodPolicy::class,
    ];

    public function register()
    {
        $this->app->make(EloquentFactory::class)->load(__DIR__.'/../../database/factories');

        $this->registerConfig();

        $this->app->bind(
            TaxDriverContract::class,
            function ($app) {
                return $app->make(config('wax.shop.tax.driver'));
            }
        );

        $taxEnvironment = (config('wax.shop.tax.avalara.environment') == 'production' ? 'production' : 'sandbox');
        $this->app->bind(
            AvaTaxClient::class,
            function ($app) use ($taxEnvironment) {
                return (new AvaTaxClient('Wax', '1.0', 'localhost', $taxEnvironment))
                    ->withSecurity(
                        config('wax.shop.tax.avalara.account_id'),
                        config('wax.shop.tax.avalara.license_key')
                    );
            }
        );

        // for the ShopServiceFacade
        $this->app->bind('shop.service', ShopService::class);

        $this->app->when(CatalogController::class)
            ->needs(FilterableRepositoryContract::class)
            ->give(ProductRepository::class);

        $this->app->when(ShopIndexer::class)
            ->needs(FilterableRepositoryContract::class)
            ->give(ProductRepository::class);

        $this->app->when(ProductRepository::class)
            ->needs(Model::class)
            ->give(config('wax.shop.models.product'));

        $this->app->when(ProductRepository::class)
            ->needs(FilterAggregatorContract::class)
            ->give(CatalogFilterAggregator::class);
    }

    public function boot()
    {
        $this->registerConsoleCommands();

        $this->registerPolicies();

        Cms::registerStructurePath(__DIR__.'/../../resources/structures');

        // route model binding for the payment methods resource controller
        Route::model('paymentmethod', config('wax.shop.models.payment_method'));

        Route::middleware('web')
            ->namespace('Wax\Shop\Http\Controllers')
            ->attribute('as', 'shop::')
            ->group(__DIR__.'/../../routes/web.php');

        $this->registerListeners();

        Gate::define('get-order', 'Wax\Shop\Policies\OrderPolicy@get');

        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations/');
        $this->loadViewsFrom(__DIR__.'/../../resources/views/', 'shop');
        $this->loadTranslationsFrom(__DIR__.'/../../resources/lang', 'shop');

        $this->app->make('Illuminate\Database\Eloquent\Factory')->load(__DIR__ . '/../../database/factories');

        // add example CSV to project
        $publicPath = public_path('uploads/shop/coupons/example_coupon_import.csv');
        $this->publishes([
            __DIR__ . '/../../resources/coupons/example_coupon_import.csv' => $publicPath
        ], 'public');
    }

    protected function registerConsoleCommands()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                Install::class,
            ]);
        }
    }

    protected function registerConfig()
    {
        $this->mergeConfigFrom(__DIR__.'/../../config/shop.php', 'wax.shop');
    }

    public function registerListeners()
    {
        // clean up the nested relations when an Order Item changes
        Item::observe(OrderItemObserver::class);
        Bundle::observe(OrderBundleObserver::class);

        Event::listen(SessionMigrationEvent::class, SessionMigrationListener::class);
        Event::listen(Login::class, LoginListener::class);

        Event::listen(
            [
                CartContentsChangedEvent::class,
                ShippingAddressChangedEvent::class,
            ],
            InvalidateOrderShippingListener::class
        );

        Event::listen(
            [
                CartContentsChangedEvent::class,
                ShippingServiceChangedEvent::class,
            ],
            RecalculateDiscountsListener::class
        );

        Event::listen(OrderChangedEventContract::class, InvalidateOrderTaxListener::class);

        foreach (config('wax.shop.listeners.place_order') as $listener) {
            Event::listen(OrderPlacedEvent::class, $listener);
        }
    }
}
