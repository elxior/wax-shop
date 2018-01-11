# Wax Shop

## Installation instructions

### Caveats
- This will change after we update to 5.5, that will allow us to do a better automated installer
- This doesn't account for seeding the admin navigation links and user privileges. Coming soon...

### Start with a working wax app
Go through the normal process to set up wax, and run the wax installer including any wax modules you want EXCEPT don't run any of the legacy shop installer modules.

### Composer install the shop package
App composer.json must include:  
```
"repositories": [
        {
            "type": "vcs",
            "url": "https://gitlab.com/oohology/wax-shop.git"
        },
        {
            "type": "vcs",
            "url": "https://github.com/oohology/AvaTax-REST-V2-PHP-SDK"
        },
        ...
    ],
"require": {
        "avalara/avataxclient": "dev-visibility",
        "oohology/wax-shop": "^1.0.0",
        ...
```
Then, you can `composer update wax-shop` and it will install the shop package.

### Register Service Provider
In `config/app.php` at end of the _Package Service Providers_ section `Wax\Shop\Providers\ShopServiceProvider::class`  

### Run the artisan commands  
`./artisan vendor:publish`  
`./artisan migrate`

### Register the shop site-search indexer
Indexers are registered in the wax-cms:site-search module's config file. You can copy it from `wax-cms/modules/site-search/config/site-search.php` to your app at `config/wax/site-search.php`, then add the shop indexer to the list:
```
    'indexers' => [
        [
            'key' => 'shop',
            'class' => 'Wax\Shop\ShopIndexer',
            'title' => 'Products',
        ],
        ...
```

