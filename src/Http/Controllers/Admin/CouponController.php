<?php

namespace Wax\Shop\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
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
        $path = $request->file('file')->getRealPath();
        $data = $this->csvToArray($path);

        foreach ($data as $record) {
            foreach ($record as $key => $val) {
                if ($val == 'NULL' || empty($val)) {
                    unset($record[$key]);
                }
            }

            Coupon::insert($record);
        }

        return Redirect::to('admin/cms/coupons');
    }

    private function csvToArray($filename)
    {
        if (!file_exists($filename) || !is_readable($filename))
            return false;

        $header = null;
        $data = array();
        if (($handle = fopen($filename, 'r')) !== false)
        {
            while (($row = fgetcsv($handle, 1000, ",", '"')) !== false)
            {
                if (!$header)
                    $header = $row;
                else
                    $data[] = array_combine($header, $row);
            }
            fclose($handle);
        }

        return $data;
    }

    /**
     * Export multiple coupons
     * Can pass the title to only get those couponsh
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function bulkExportCoupons(Request $request)
    {
        $selectedFields = [
            'code',
            'expired_at',
            'title',
            'dollars',
            'percent',
            'minimum_order',
            'one_time',
            'include_shipping',
            'created_at',
            'updated_at',
        ];

        if (!empty($request->get('title'))) {
            $data = Coupon::select($selectedFields)->where('title', $request->get('title'))->get()->toArray();
        } else {
            $data = Coupon::select($selectedFields)->get()->toArray();
        }

        if ($data === false || count($data) <= 0) {
            return Redirect::to('admin/cms/coupons');
        }

        if (!is_dir(storage_path('coupons'))) {
            mkdir(storage_path('coupons'));
        }

        $fileName = storage_path('coupons/coupons_export.csv');

        $out = fopen($fileName, 'w');
        if ($out === false) {
            return Redirect::to('admin/cms/coupons');
        }

        fputcsv($out, array_keys($data[1]));
        foreach($data as $line)
        {
            fputcsv($out, $line);
        }
        fclose($out);

        return response()->download($fileName)->deleteFileAfterSend(true);
    }
}