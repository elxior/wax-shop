@extends('admin::layouts.base')

@section('head')

@stop

@section('body')
    <div class="cms-edit-box group">
        <div class="edit-heading icon-edit">
            Bulk Generate Coupons
        </div>
        <div class="edit_body group">
            <form enctype="multipart/form-data" action="{{ URL::route('shop::coupons::generate') }}" method="post">
                {{ csrf_field() }}
                <div class="cms-col-wide">
                    @if (!empty($error_msg))
                        @foreach ($error_msg as $msg)
                        <div style="color: #ff0000; font-weight: bold;">
                            {{ $msg }}
                        </div>
                        @endforeach
                        <br>
                    @endif

                    <div style="color: green; font-weight: bold;">
                    </div>

                    <div class="edit-container field-container">
                        <div class="field-heading"><label class="cmsFieldLabel" for="title">Title</label></div>
                        <div class="body-container">
                            <div class="inputContainer" id="inputContainer_title">
                                <input type="text" name="title" id="title" value="{{ $title or '' }}">
                            </div>
                        </div>
                        <div style="clear: both;"></div>
                    </div>

                    <div class="edit-container group">
                        <label class="cmsFieldLabel">Discount</label>
                        <div class="body-container">
                            <table><tbody><tr>
                                    <td class="field-container">
                                        <div class="inputContainer" id="inputContainer_percent">
                                            <label for="percent">
                                                Percent Off
                                            </label>
                                            <input type="number" name="percent" id="percent" value="{{ $percent or '0' }}" min="0" max="50" step="1">
                                            <div class="cms_notes"></div>
                                        </div>

                                    </td>
                                    <td class="field-container">
                                        <div class="inputContainer" id="inputContainer_dollars">
                                            <label for="dollars">
                                                Dollars Off
                                            </label>
                                            <input type="number" name="dollars" id="dollars" value="{{ $dollars or '0.00' }}" min="0" step="0.01">
                                            <div class="cms_notes"></div>
                                        </div>

                                    </td>
                                    <td class="field-container">
                                        <div class="inputContainer" id="inputContainer_minimum_order">
                                            <label for="minimum_order">
                                                Minimum Order
                                            </label>
                                            <input type="number" name="minimum_order" id="minimum_order" value="{{ $minimum_order or '0.00' }}" min="0" step="0.01">
                                            <div class="cms_notes"></div>
                                        </div>

                                    </td>
                                </tr></tbody></table>
                        </div>
                        <div style="clear: both;"></div>
                    </div>


                    <!-- Group Coupon Code -->
                    <div class="edit-container group">
                        <label class="cmsFieldLabel">Coupon Codes</label>
                        <div class="body-container">
                            <table><tbody><tr>
                                    <td class="field-container">
                                        <div class="inputContainer" id="inputContainer_code">
                                            <label for="quantity">Quantity</label>
                                            <input type="text" name="quantity" id="quantity" style="width: 50px;" value="{{ $quantity or '' }}">
                                            <div class="cms_notes">
                                                <em>Number of codes to generate</em>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="field-container">
                                        <div class="inputContainer" id="inputContainer_expired_at">
                                            <label for="expired_at">
                                                Expiration Date
                                            </label>
                                            <input type="text" name="expired_at" id="expired_at" value="{{ $expired_at or '' }}" style="max-width: 250px;" class="flatpickr-input active">

                                            <script type="text/javascript">
                                                $(function(){
                                                    var datepicker = new Flatpickr($("#expired_at").get()[0], {
                                                        enableTime: true,
                                                        allowInput: true,
                                                    });
                                                });
                                            </script>
                                        </div>

                                    </td>
                                    <td class="field-container">
                                        <div class="inputContainer" id="inputContainer_one_time">
                                            <label for="one_time">One-Time Use</label>
                                            <input type="checkbox" name="one_time" id="one_time" value="1">

                                            <div class="cms_notes">One-Time Use</div>
                                        </div>
                                    </td>
                                </tr></tbody></table>
                        </div>
                        <div style="clear: both;"></div>
                    </div> <!-- // Group Coupon Code -->
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