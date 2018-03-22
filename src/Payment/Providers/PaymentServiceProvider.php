<?php

namespace Wax\Shop\Payment\Providers;

use Illuminate\Support\ServiceProvider;
use Omnipay\AuthorizeNet\CIMGateway;
use Omnipay\Omnipay;
use Wax\Shop\Payment\Drivers\AuthorizeNetCimDriver;

class PaymentServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(CIMGateway::class, function ($app) {
            return $this->getAuthorizeCimGateway();
        });
    }

    public function boot()
    {
        //
    }

    protected function getAuthorizeCimGateway()
    {
        if (empty(config('wax.shop.payment.drivers.authorizenet_cim.api_login_id'))
            || empty(config('wax.shop.payment.drivers.authorizenet_cim.transaction_key'))
        ) {
            throw new \Exception(
                __('shop::payment.driver_not_configured', ['name' => 'Authorize.net CIM'])
            );
        }

        $gateway = Omnipay::create('AuthorizeNet_CIM');
        $gateway->setApiLoginId(config('wax.shop.payment.drivers.authorizenet_cim.api_login_id'));
        $gateway->setTransactionKey(config('wax.shop.payment.drivers.authorizenet_cim.transaction_key'));

        if (config('wax.shop.payment.drivers.authorizenet_cim.developer_mode')) {
            $gateway->setDeveloperMode(true);
        } elseif (config('wax.shop.payment.drivers.authorizenet_cim.test_mode')) {
            $gateway->setTestMode(true);
        }

        return $gateway;
    }
}
