<section>
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
