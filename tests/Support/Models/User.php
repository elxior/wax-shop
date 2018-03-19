<?php

namespace Tests\Shop\Support\Models;

use App\User as UserBase;
use Wax\Shop\Traits\ShopUser;

class User extends UserBase
{
    use ShopUser;
}
