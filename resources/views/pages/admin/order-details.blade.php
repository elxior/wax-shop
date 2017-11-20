@extends('admin::layouts.base')

@section('head')
    @if (File::exists(public_path() . '/assets/css/wysiwyg-styles.css'))
        <link rel="stylesheet" type="text/css" href="/assets/css/wysiwyg-styles.css">
    @endif
@stop

@section('body')
    {!! csrf_field() !!}
    <div class="cms-edit-box group">
        <div class="edit-heading icon-edit">
                <div style="float: right;">
                    <a target="_blank" href="cms_print.php?structure={{ $structure }}&id={{ $id }}"><span style="font-size: 16px;">Print Order</span></a>
                </div>
            {{ $page['title'] }}
        </div>
        <div class="edit_body group">
            @if (!empty($errors))
                @include('admin::components.shared.error-list', ['errors' => $errors])
            @endif

            @if (!empty($notes))
                @include('admin::components.shared.notes', ['notes' => $notes])
            @endif

            <div class="cms-col-wide">
                <h3>
                    Reference Number: {{ $order['sequence'] }}
                </h3>
                <p>
                    Placed: {{ date('F j, Y @ g:ia', $order['placed_at']) }}
                </p>
                <div class="order-items">
                    <table class="cart-style account-order-details" id="cart-summary">
                        <thead>
                            <tr>
                                <th>
                                    SKU
                                </th>
                                <th>
                                    Name
                                </th>
                                <th>
                                    Unit Price
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($order['items'] as $item)
                            <tr data-id="$item['id']">
                                <td class="cartitem-sku">
                                    {{ $item['sku'] }}
                                </td>
                                <td class="cartitem-name">
                                    {{ $item['name'] }}
                                </td>
                                <td class="cartitem-unit-price">
                                    {{ $item['unit_price'] }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="4">
                                    <hr>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="4">
                                    {{--
                                        <section class="order-address billing-address">
                                            <a href="#" class="editLink" data-action="modal/ajax" data-href="cms_edit_modal.php?structure=users_address_book_embedded&amp;id=1234199">
                                                <h3>Billing Address</h3>
                                                <p>firstname lastname<br>
                                                address1<br>
                                                city, state zip<br>
                                                country<br>
                                                <br>
                                                email<br>
                                                phone<br>
                                                </p>
                                            </a>
                                        </section>
                                    --}}
                                    <section class="order-address shipping-address">
                                        <h3>Shipping Address</h3>
                                        <p>{{ $order['default_shipment']['firstname'] }} {{ $order['default_shipment']['lastname'] }}<br>
                                        {{ $order['default_shipment']['address1'] }}<br>
                                        @if(!empty($order['default_shipment']['address2']))
                                        {{ $order['default_shipment']['address2'] }}<br>
                                        @endif
                                        {{ $order['default_shipment']['city'] }}, {{ $order['default_shipment']['state'] }} {{ $order['default_shipment']['zip'] }}<br>
                                        <br>
                                        {{ $order['default_shipment']['email'] }}<br>
                                        {{ $order['default_shipment']['phone'] }}<br>
                                        </p>
                                    </section>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="4">
                                    <section class="order-payment-info">
                                        <h3>Payment Information</h3>
                                        <div class="order-totals">
                                            <div id="cart-subtotal"><em>Subtotal:</em> 66.00</div>

                                            
                                            <div id="order-taxshipping">
                                                <em>Shipping:</em> <span data-property="tax-shipping-amount">7.00</span><br>
                                                <em>Tax:</em> <span data-property="tax-shipping-amount">3.96</span>
                                                </div>

                                            <div class="order-total"><em>Total:</em> <strong>76.96</strong></div>
                                        </div>
                                        <div class="cc-info">
                                            <em>visa XXXX1111:</em> 76.96
                                            <br>
                                            <em> &nbsp; - Processor: FirstData_Payeezy</em><br>
                                            <em> &nbsp; - Ref: ET158507::2226861131</em><br>
                                            <em> &nbsp; - Auth Code: 1030821355391111</em><br>
                                        </div>
                                    </section>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <div class="cms-col-thin">
                <div class="edit-container">
                    <label class="cmsFieldLabel">Mark As Shipped</label>
                    <div class="body-container">
                        <div class="save">
                            <button class="button" type="button" data-action="save" name="action">Mark</button>
                        </div>
                    </div>
                </div>
                <div class="edit-container">
                    <label class="cmsFieldLabel">More Processing</label>
                    <div class="body-container">
                        <div class="save">
                            <button class="button" type="button" data-action="save" name="action">Process</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
