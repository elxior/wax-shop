<?php

namespace App\Shop\Providers;

use App\Shop\Contracts\OrderChangedEventContract;
use App\Shop\Contracts\Tax\TaxDriverContract;
use App\Shop\Events\OrderChanged\CartContentsChangedEvent;
use App\Shop\Events\OrderChanged\ShippingAddressChangedEvent;
use App\Shop\Events\OrderChanged\ShippingServiceChangedEvent;
use App\Shop\Filters\CatalogFilterAggregator;
use App\Shop\Http\Controllers\CatalogController;
use App\Shop\Listeners\InvalidateOrderShippingListener;
use App\Shop\Listeners\InvalidateOrderTaxListener;
use App\Shop\Listeners\LoginListener;
use App\Shop\Listeners\RecalculateCouponValueListener;
use App\Shop\Listeners\SessionMigrationListener;
use App\Shop\Models\Order\Item;
use App\Shop\Models\Product;
use App\Shop\Observers\OrderItemObserver;
use App\Shop\Repositories\ProductRepository;
use App\Shop\Services\ShopService;
use Avalara\AvaTaxClient;
use Illuminate\Auth\Events\Login;
use Illuminate\Database\Eloquent\Model;
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

        Route::middleware('web')
            ->namespace('App\Shop\Http\Controllers')
            ->attribute('as', 'shop::')
            ->group(app_path('Shop/routes/web.php'));
    }

    public function boot()
    {
        $this->registerListeners();

        Gate::define('get-order', 'App\Shop\Policies\OrderPolicy@get');

        Item::observe(OrderItemObserver::class);

        $this->loadViewsFrom(app_path('Shop/resources/views/'), 'shop');
    }

    protected function registerConfig()
    {
        $this->mergeConfigFrom(app_path('Shop/config/shop.php'), 'wax.shop');
        $this->mergeConfigFrom(app_path('Shop/config/tax.php'), 'wax.shop.tax');
    }

    public function registerListeners()
    {
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
