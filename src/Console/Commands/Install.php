<?php

namespace Wax\Shop\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class Install extends Command
{
    protected $signature = 'shop:install';

    protected $description = 'Seed the database for admin navigation, permissions, etc.';

    protected $installer;

    public function __construct()

    public function handle()
    {
        //DB::table('admin_navigation')
    }


}
