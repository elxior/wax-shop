@extends('admin::layouts.base')

@section('head')
    <!--
        NOTE: I'm doing this embedded style as a temporary hack. I expect a fed will want to rip this out
        and overhaul the css and probably the markup.
    -->
    <style type="text/css">
        section {
            margin-top: 30px;
            margin-bottom: 10px;
        }
        #cart-summary thead th {
            text-align: left;
        }
        ul {
            list-style-type: none;
        }
    </style>
@endsection

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
                <ul>
                    <li>Placed: {{ $order->placed_at->format('F j, Y @ g:ia') }}</li>
                    <li>
                        Customer:
                        @if ($order->user_id)
                            <a href="{{ route('admin::editRecord', ['structure' => 'users', 'id' => $order->user_id]) }}">{{ $order->email }}</a>
                        @else
                            {{ $order->email }} (Guest Checkout)
                        @endif
                    </li>
                </ul>

                @foreach ($order->shipments as $shipment)
                    @include ('shop::components.admin.order-details.shipment', ['shipment' => $shipment])
                @endforeach

                <section class="order-payment-info">
                    <h3>Total</h3>
                    <ul>
                        @if ($order->gross_total != $order->total)
                            <li>Cart Subtotal: {{ Currency::format($order->gross_total) }}</li>
                        @endif

                        @if ($order->shipping_gross_subtotal > 0)
                            <li>Shipping Subtotal: {{ Currency::format($order->shipping_gross_subtotal) }}</li>
                        @endif

                        @if ($order->tax_subtotal > 0)
                            <li>Tax Subtotal: {{ Currency::format($order->tax_subtotal) }}</li>
                        @endif

                        @foreach ($order->bundles as $bundle)
                            <li>{{ $bundle->name }}: {{ Currency::format($bundle->calculated_value) }}</li>
                        @endforeach

                        @if (!empty($order->coupon))
                            <li>{{ $order->coupon->title }} '{{ $order->coupon->code }}': {{ Currency::format($order->coupon->calculated_value) }}</li>
                        @endif

                        <li>Total: {{ Currency::format($order->total) }}</li>
                        <li>Applied Payments: {{ Currency::format($order->payment_total) }}</li>
                        <li>Balance Due: {{ Currency::format($order->balance_due) }}</li>
                    </ul>
                </section>

                <section class="order-payment-info">
                    <h3>Payment Details</h3>

                    @foreach ($order->payments as $payment)
                        @if ($payment->type == 'Credit Card')
                            @include ('shop::components.admin.order-details.payment.credit-card', ['payment' => $payment])
                        @else
                            <?php throw new \Exception('unhandled payment type: ' . $payment->type) ?>
                        @endif
                    @endforeach
                </section>
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
