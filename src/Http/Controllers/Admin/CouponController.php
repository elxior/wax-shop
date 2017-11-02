<?php

namespace Wax\Shop\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Redirect;
use Wax\Shop\Models\Coupon;

/**
 * Class CouponController
 * @package Wax\Shop\Http\Controllers\Admin
 */
class CouponController
{
    /**
     * Generate multiple coupons from given data
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function bulkGenerateCoupons(Request $request)
    {
        /*
         * Array
        (
            [_token] => LeRTpmSdxSSKYsRXUbXqJy8HYCSaw48EJui9lkLY
            [title] => Bulk Generated Coupons
            [percent] => 50
            [dollars] => 0.00
            [minimum_order] => 0.00
            [quantity] => 5
            [expired_at] => 2017-12-01 12:00:00
            [one_time] => 1
            [action] => Generate
        )
         */
        $title = $request->get('title');
        $qty = (int)$request->get('quantity');
        $percent = $request->get('percent');
        $dollars = $request->get('dollars');
        $minimum = $request->get('minimum');
        $expired_at = $request->get('expired_at');
        $inputDate = $request->get('exp_date');
        $oneTime = (int)$request->get('one_time');

        if($title == '') {
            $error_msg[] = 'Please enter a Title.';
        }
        if($dollars == 0 && $percent == 0) {
            $error_msg[] = 'Please set either "Dollars Off" or "Percent Off".';
        }
        if($dollars> 0 && $percent > 0) {
            $error_msg[] = 'You may set only one of "Dollars Off" or "Percent Off".';
        }
        if($qty == 0) {
            $error_msg[] = 'Please enter a Quantity.';
        }
        if(empty($error_msg)) {
            // pull current Codes
            $currentCodes = Coupon::select('code')->get()->toArray();
            $chars = 'BC2DF3GH4JK6MP7QR8TV9WXY';
            $numChars = strlen($chars) - 1;
            $newCodes = [];
            for($n = 1; $n <= $qty; $n++) {
                $newCode = '';
                for($c = 1; $c <= 8; $c++) {
                    $r = rand(0, $numChars);
                    $newCode .= substr($chars, $r, 1);
                }
                if(in_array($newCode, $currentCodes)) {
                    $n--;
                } else {
                    $newCodes[] = $newCode;
                }
            }

            foreach ($newCodes as $code)
            {
                $newCoupon = new Coupon;
                $newCoupon->percent = $percent;
                $newCoupon->title = $title;
                $newCoupon->expired_at = $expired_at;
                $newCoupon->dollars = $dollars;
                $newCoupon->minimum_order = $minimum;
                $newCoupon->code = $code;
                $newCoupon->one_time = $oneTime;

                $newCoupon->save();
            }
        }


        return Redirect::to('admin/cms/coupons');
    }

    /**
     * Import multiple coupons
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function bulkImportCoupons(Request $request)
    {
        return Redirect::to('admin/cms/coupons');
    }

    /**
     * Export multiple coupons
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function bulkExportCoupons(Request $request)
    {
        return Redirect::to('admin/cms/coupons');
    }
}