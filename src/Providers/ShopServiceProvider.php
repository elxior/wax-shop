<?php

namespace Wax\Shop\Providers;

use Wax\Shop\Contracts\OrderChangedEventContract;
use Wax\Shop\Contracts\Tax\TaxDriverContract;
use Wax\Shop\Events\OrderChanged\CartContentsChangedEvent;
use Wax\Shop\Events\OrderChanged\ShippingAddressChangedEvent;
use Wax\Shop\Events\OrderChanged\ShippingServiceChangedEvent;
use Wax\Shop\Filters\CatalogFilterAggregator;
use Wax\Shop\Http\Controllers\CatalogController;
use Wax\Shop\Listeners\InvalidateOrderShippingListener;
use Wax\Shop\Listeners\InvalidateOrderTaxListener;
use Wax\Shop\Listeners\LoginListener;
use Wax\Shop\Listeners\RecalculateCouponValueListener;
use Wax\Shop\Listeners\SessionMigrationListener;
use Wax\Shop\Models\Order\Item;
use Wax\Shop\Models\Product;
use Wax\Shop\Observers\OrderItemObserver;
use Wax\Shop\Repositories\ProductRepository;
use Wax\Shop\Services\ShopService;
use Avalara\AvaTaxClient;
use Illuminate\Auth\Events\Login;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factory as EloquentFactory;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Wax\Core\Contracts\FilterAggregatorContract;
use Wax\Core\Contracts\FilterableRepositoryContract;
use Wax\Core\Events\SessionMigrationEvent;

class ShopServiceProvider extends ServiceProvider
{
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

        $this->app->bind(
            AvaTaxClient::class,
            function ($app) {
                return (new AvaTaxClient('Wax', '1.0', 'localhost', 'sandbox'))
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

        $this->app->when(ProductRepository::class)
            ->needs(Model::class)
            ->give(Product::class);

        $this->app->when(ProductRepository::class)
            ->needs(FilterAggregatorContract::class)
            ->give(CatalogFilterAggregator::class);
    }

    public function boot()
    {
        Route::middleware('web')
            ->namespace('Wax\Shop\Http\Controllers')
            ->attribute('as', 'shop::')
            ->group(__DIR__.'/../../routes/web.php');

        $this->registerListeners();

        Gate::define('get-order', 'Wax\Shop\Policies\OrderPolicy@get');

        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations/');
        $this->loadViewsFrom(__DIR__.'/../../resources/views/', 'shop');
    }

    protected function registerConfig()
    {
        $this->mergeConfigFrom(__DIR__.'/../../config/shop.php', 'wax.shop');
        $this->mergeConfigFrom(__DIR__.'/../../config/tax.php', 'wax.shop.tax');
    }

    public function registerListeners()
    {
        Item::observe(OrderItemObserver::class);

        Event::listen(SessionMigrationEvent::class, SessionMigrationListener::class);
        Event::listen(Login::class, LoginListener::class);

        Event::listen(CartContentsChangedEvent::class, InvalidateOrderShippingListener::class);
        Event::listen(ShippingAddressChangedEvent::class, InvalidateOrderShippingListener::class);

        Event::listen(
            [
                CartContentsChangedEvent::class,
                //ShippingAddressChangedEvent::class,
                ShippingServiceChangedEvent::class,
            ],
            RecalculateCouponValueListener::class
        );

        Event::listen(OrderChangedEventContract::class, InvalidateOrderTaxListener::class);
    }
}
