<?php

namespace Wax\Shop\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Redirect;
use Wax\Shop\Models\Coupon;

/**
 * Class CouponController
 * @package Wax\Shop\Http\Controllers\Admin
 */
class CouponController
{
    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function showGenerateForm()
    {
        return view('shop::pages.admin.coupons.bulk_generate');
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function showImportForm()
    {
        return view('shop::pages.admin.coupons.bulk_import');
    }

    /**
     * Generate multiple coupons from given data
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function bulkGenerateCoupons(Request $request)
    {
        $validator = $this->getGeneratorValidator($request->all());

        if ($validator->fails()) {
            return redirect()
                ->back()
                ->withInput($request->all())
                ->withErrors($validator->errors());
        }

        // pull current Codes
        $currentCodes = Coupon::select('code')->get()->toArray();
        $chars = 'BC2DF3GH4JK6MP7QR8TV9WXY';
        $numChars = strlen($chars) - 1;
        $newCodes = [];
        for ($n = 1; $n <= $request->get('quantity', 0); $n++) {
            $newCode = '';
            for ($c = 1; $c <= 8; $c++) {
                $r = rand(0, $numChars);
                $newCode .= substr($chars, $r, 1);
            }

            if (in_array($newCode, $currentCodes)) {
                $n--;
            } else {
                $newCodes[] = $newCode;
            }
        }

        foreach ($newCodes as $code) {
            $newCoupon = new Coupon;

            $newCoupon->percent = (int)$request->get('percent', 0);
            $newCoupon->title = $request->get('title');
            $newCoupon->expired_at = $request->has('expired_at') ? $request->input('expired_at').':00' : null;
            $newCoupon->dollars = (float)$request->get('dollars', 0);
            $newCoupon->minimum_order = (int)$request->get('minimum_order', 0);
            $newCoupon->code = $code;
            $newCoupon->one_time = (bool)$request->get('one_time');
            $newCoupon->include_shipping = (bool)$request->get('include_shipping');

            $newCoupon->save();
        }

        $request->session()->flash('message', 'Successfully generated '.$request->get('quantity').' coupons.');
        return Redirect::to('admin/cms/coupons');
    }

    protected function getGeneratorValidator(array $data)
    {
        $validator = Validator::make($data, [
            'title' => 'required|max:255',
            'dollars' => 'required_without:percent|numeric',
            'percent' => 'max:50|numeric',
            'quantity' => 'required|numeric'
        ]);

        $validator->sometimes('dollars', 'numeric|required|min:.01', function ($input) {
            return $input->percent == 0;
        });

        return $validator;
    }

    /**
     * Import multiple coupons
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function bulkImportCoupons(Request $request)
    {
        if ($request->get('action') == 'Cancel') {
            return Redirect::to('admin/cms/coupons');
        }

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

        $request->session()->flash('message', 'Successfully imported '.count($data).' coupons.');

        return Redirect::to('admin/cms/coupons');
    }

    /**
     * Method for converting CSV to an array
     *
     * @param $filename
     * @return array|bool
     */
    private function csvToArray($filename)
    {
        if (!file_exists($filename) || !is_readable($filename)) {
            return false;
        }

        $header = null;
        $data = array();
        if (($handle = fopen($filename, 'r')) !== false) {
            while (($row = fgetcsv($handle, 1000, ",", '"')) !== false) {
                if (!$header) {
                    $header = $row;
                } else {
                    $data[] = array_combine($header, $row);
                }
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
            $data = Coupon::withoutGlobalScopes()
                ->select($selectedFields)
                ->where('title', $request->get('title'))
                ->get()
                ->toArray();
        } else {
            $data = Coupon::withoutGlobalScopes()
                ->select($selectedFields)
                ->get()
                ->toArray();
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

        fputcsv($out, array_keys(current($data)));
        foreach ($data as $line) {
            fputcsv($out, $line);
        }
        fclose($out);

        return response()->download($fileName)->deleteFileAfterSend(true);
    }
}
