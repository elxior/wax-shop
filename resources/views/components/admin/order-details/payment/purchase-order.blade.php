<div class="cc-info">
    <ul>
        <li>
            @if (is_null($payment->captured_at))
                Pending Purchase Order
            @else
                <strong>
                    {{ $payment->brand }} XXXX{{ substr($payment->account, -4) }}:
                    {{ Currency::format($payment->amount) }}
                </strong>
            @endif
        </li>
        <li><em>Billing Address:</em><br> {!! \Wax\Data::formatAddress($payment) !!}</li>
    </ul>
</div>
