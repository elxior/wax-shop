<?php

namespace Wax\Shop\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Redirect;

class CouponController
{
    public function bulkGenerateCoupons(Request $request)
    {
        return Redirect::to('admin/cms/coupons');
    }

    public function bulkImportCoupons(Request $request)
    {
        return Redirect::to('admin/cms/coupons');
    }

    public function bulkExportCoupons(Request $request)
    {
        return Redirect::to('admin/cms/coupons');
    }
}