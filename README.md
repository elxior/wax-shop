# Wax Shop

## Installation instructions

### Start with a working wax app
Go through the normal process to set up wax, and run the wax installer including any wax modules you want.

### Composer install the shop package
App composer.json must include:  
```
"repositories": [
        {
            "type": "vcs",
            "url": "https://gitlab.com/oohology/wax-shop.git"
        },
        ...
    ],
"require": {
        "oohology/wax-shop": "^2.0.0",
        ...
```
Then, you can `composer update wax-shop` and it will install the shop package.

### Run the artisan commands  
`./artisan vendor:publish`  
`./artisan migrate`  
`./artisan shop:install`  

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

### Alavara - Avatax driver
App composer.json must include:  
```
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/oohology/AvaTax-REST-V2-PHP-SDK"
        },
        ...
    ],
    "require": {
        "avalara/avataxclient": "dev-visibility",
        ...
```
Then, in the shop config file, set tax.driver to `Wax\Shop\Tax\Drivers\AvalaraDriver::class`. Also see the config file for the required ENV variables for avalara api keys.

