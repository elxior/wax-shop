@extends('core::emails.base')

@section('body')
    <table class="email" style="background-color: #FFF; font-family: sans-serif; color: #273C89; width: 100%;"
           width="100%" cellpadding="40">
        <tbody>
        <tr>
            <td>
                <h1 class="email__headline" style="margin-top: 0px; color: #273C89; font-size: 32px;">Your order has
                    shipped!</h1>
                <h2 class="email__subhead" style="color: #273C89; font-size: 24px;">Order #{{ $order['sequence'] }}</h2>

                @if (!empty($trackedShipments))
                    <strong>Tracking Number</strong>
                    @foreach ($trackedShipments as $shipment)
                        <p style="color: #273C89;">
                            @if (!empty($shipment['shipping_service_name']))
                                {{ $shipment['shipping_carrier'] }} {{ $shipment['shipping_service_name'] }}:
                            @else
                                Shipment {{ $loop->iteration }}:
                            @endif
                            {{ $shipment['tracking_number'] }}
                        </p>
                    @endforeach
                @endif
                <hr>
                @foreach ($order['payments'] as $payment)
                    <p style="color: #273C89;">
                        Your {{ $payment['brand'] }} card ending in {{ substr($payment['account'], -4) }} was
                        charged {{ Currency::format($payment['amount']) }}
                        on {{ \Carbon\Carbon::parse($payment['authorized_at'])->format('F j, Y') }}
                    </p>
                @endforeach
                <table class="email-cart" width="100%" style="color: #273C89;" cellpadding="0" cellspacing="0">
                    <tr style="background-color: #E9ECEF;">
                        <td class="email-cart__heading" style="font-weight: 700; padding: 16px;">Order
                            #{{ $order['sequence'] }}</td>
                        <td class="email-cart__date"
                            style="padding: 16px;">{{ \Carbon\Carbon::parse($order['placed_at'])->format('m/j/y') }}</td>
                    </tr>
                    @foreach ($order['items'] as $item)
                        <tr style="background-color: #F8F9FA;">
                            <td class="email-cart__product-name"
                                style="padding: 12px 16px; border-bottom: 1px solid #E9ECEF;">
                                <a style="color: #273C89;" href="{{ $item['url'] }}" class="email-cart__product-link">
                                    {{ $item['name'] }}
                                </a>
                            </td>
                            <td class="email-cart__product-val"
                                style="padding: 12px 16px; border-bottom: 1px solid #E9ECEF;">{{ Currency::format($item['subtotal']) }}</td>
                        </tr>
                    @endforeach
                </table>
                <table width="100%" cellpadding="20">
                    <tr>
                        <td colspan="2" align="right">
                            <ul style="list-style: none;">
                                <li style="color: #273C89; font-weight: 700; margin-bottom: 8px;">
                                    Subtotal: {{ Currency::format($order['item_gross_subtotal']) }}</li>
                                @foreach ($order['bundles'] as $bundle)
                                    <li style="color: #0CA678; margin-bottom: 4px;">{{ $bundle['name'] }}:
                                        -{{ Currency::format($bundle['calculated_value']) }}</li>
                                @endforeach

                                @if (!empty($order['coupon']))
                                    <li style="color: #0CA678; margin-bottom: 4px;">{{ $order['coupon']['title'] }}
                                        '{{ $order['coupon']['code'] }}':
                                        -{{ Currency::format($order['coupon']['calculated_value']) }}</li>
                                @endif

                                @if ($order['tax_subtotal'] > 0)
                                    <li style="color: #495057; margin-bottom: 4px;">Sales Tax
                                        ({{ $order['default_shipment']['tax_desc'] }}):
                                        + {{ Currency::format($order['tax_subtotal']) }}</li>
                                @endif

                                <li style="color: #EFA420; font-size: 18px; font-weight: 700; margin-top: 8px;">
                                    Total: {{ Currency::format($order['total']) }}</li>
                            </ul>
                        </td>
                    </tr>
                </table>
                <table class="email-cart" width="100%" style="color: #273C89;" cellpadding="10" cellspacing="0">
                    <tr>
                        <td align="center">
                            <h2 class="email__subhead" style="color: #273C89; font-size: 24px;">Start Accessing Your New
                                Products Now!</h2>
                            <a href="#" class="email__start-learning-cta"
                               style="display: inline-block; width: 203px; height: 40px; margin: 0 auto;">
                                <img src="{{ asset('res/images/email/cta__start-learning.png') }}" alt="Start Learning"
                                     width="203" height="40">
                            </a>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        </tbody>
    </table>
@endsection
