<?php

namespace Wax\Shop\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Wax\Core\Services\Installer;
use Wax\Shop\Models\Tax;

class Install extends Command
{
    protected $signature = 'shop:install';

    protected $description = 'Seed the database for admin navigation, permissions, etc.';

    /**
     * Wax package installation helper
     * @var Installer
     */
    protected $installer;

    public function __construct(Installer $installer)
    {
        parent::__construct();

        $this->installer = $installer;
    }

    public function handle()
    {
        $this->createAdminNavigation();
        $this->createPrivileges();

        $this->info('Create Default Tax Zone - "KY 6%"');
        Tax::updateOrCreate(['zone' => 'KY'], [
            'rate' => 6,
            'tax_shipping' => true
        ]);
    }

    protected function createAdminNavigation()
    {
        $this->info('Create Admin Navigation');

        $this->createLink('Shop', 'Products', '/admin/cms/products');
        $this->createLink('Shop', 'Orders', '/admin/cms/orders');
        $this->createLink('Shop', 'Coupons', '/admin/cms/coupons');
        $this->createLink('Shop', 'Gift Cards', '/admin/cms/gift_cards');
        $this->createLink('Shop', 'Brands', '/admin/cms/product_brands');
        $this->createLink('Shop', 'Categories', '/admin/cms/product_categories');
        $this->createLink('Shop', 'Tax Table', '/admin/cms/tax');
    }

    protected function createPrivileges()
    {
        $administratorGroups = ['Administrator', 'Manager'];

        $this->info('Grant Privileges');

        $this->grant('Shop - Products', $administratorGroups);
        $this->grant('Shop - Orders', $administratorGroups);
        $this->grant('Coupons', $administratorGroups);
        $this->grant('Gift Cards', $administratorGroups);
        $this->grant('Tax Table', $administratorGroups);
    }

    protected function createLink($parentName, $linkName, $linkUrl = '', bool $active = true)
    {
        $this->info(" ... {$parentName} > {$linkName}");
        $this->installer->createAdminNavigation($parentName, $linkName, $linkUrl, $active);
    }

    protected function grant($privilegeName, array $groupNames)
    {
        foreach ($groupNames as $groupName) {
            $this->info(" ... {$privilegeName} -> {$groupName}");
            $this->installer->grant($privilegeName, $groupName);
        }
    }
}
