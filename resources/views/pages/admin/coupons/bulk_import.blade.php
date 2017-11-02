@extends('admin::layouts.base')

@section('head')

@stop

@section('body')
    <div class="cms-edit-box group">
        <div class="edit-heading icon-edit">
            Bulk Import Coupons
        </div>
        <div class="edit_body group">
            <form enctype="multipart/form-data" action="{{ URL::route('shop::coupons.bulk.import') }}" method="post">
                {{ csrf_field() }}
                <div class="cms-col-wide">
                    <div style="color: #ff0000; font-weight: bold;">
                    </div>

                    <div style="color: green; font-weight: bold;">
                    </div>
                </div>

                <div style="width: 65%;">
                    <p>
                        Use the upload field below to upload an <em>.csv</em> file to import new coupons.  Please reference the provided example to ensure proper formatting -
                        (<a href="{{ URL::to('res/uploads/shop/coupons/example_coupon_import.csv') }}">Example <em>.csv</em> file</a>).
                    </p>
                </div>

                <div class="cms-col-wide">
                    <label for="file">Select a CSV file:</label>
                    <input type="file" name="file" id="file"><br>
                </div>
                <div style="clear: both;"><br><br></div>

                <div class="">
                    <input type="submit" name="action" value="Upload" class="button">
                    <input class="button" type="submit" name="action" value="Cancel">
                </div>
                <div style="clear: both;"></div>
            </form>
        </div>
    </div>
@stop