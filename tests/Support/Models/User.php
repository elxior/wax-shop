<?php

namespace Tests\Shop\Support\Models;

use Wax\Core\Eloquent\Models\User as UserBase;
use Wax\Shop\Traits\ShopUser;

class User extends UserBase
{
    use ShopUser;
}
