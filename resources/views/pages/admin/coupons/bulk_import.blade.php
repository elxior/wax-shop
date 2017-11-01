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



                <div class="cms-col-thin">
                    <div class="edit-container">
                        <label class="cmsFieldLabel">Action</label>
                        <div class="body-container">
                            <div class="save">
                                <input type="submit" name="action" value="Generate" class="button">
                                <input class="button" type="submit" name="action" value="Cancel">
                            </div>
                        </div>
                    </div>
                </div>
                <div style="clear: both;"></div>
            </form>
        </div>
    </div>
@stop