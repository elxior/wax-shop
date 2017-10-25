@extends('admin::layouts.base')

@section('head')
    <script type="text/javascript">
        function hasPendingChanges() {
            $(window).bind('beforeunload', function() {
                return 'There are pending changes on this page that will be discarded if you cancel.';
            });
        }
        function cancelLeavePageAlert() {
            window.onbeforeunload = null;
            $(window).unbind('beforeunload');
        }
        $().ready(function() {
            $("#cmsEditForm input[type=text]").change(hasPendingChanges);
            $(".cancelLeavePageAlert").click(function() {
                cancelLeavePageAlert();
            });
        });
    </script>
@stop

@section('body')
    <div class="cms-edit-box group">
        <div class="edit-heading icon-edit">
            {{ $product->name }} - Modifiers
        </div>
        <div class="edit_body group">
            <form method="post" id="cmsEditForm" action="">
                {{ csrf_field() }}
                {{ method_field('PUT') }}
                <input type="hidden" name="id" value="{{ $product->id }}">

                <div class="cms-col-wide">
                    @if (empty($options))
                        <em>There are no options for this product.</em>
                    @else
                        @include('shop::components.admin.product-modifiers.table', ['options' => $options])
                    @endif
                </div>

                <div class="cms-col-thin">
                    <div class="edit-container">
                        <label class="cmsFieldLabel">Save</label>
                        <div class="body-container">
                            <div class="save">
                                <input class="cancelLeavePageAlert" type="submit" name="action" value="Save" />
                                <input type="submit" name="action" value="Cancel" />
                            </div>
                        </div>
                    </div>
                </div>

            </form>
        </div>
    </div>
@stop