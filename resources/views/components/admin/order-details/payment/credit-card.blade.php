<div class="cc-info">
    <ul>
        <li>
            <strong>
                {{ $payment->brand }} XXXX{{ substr($payment->account, -4) }}:
                {{ Currency::format($payment->amount) }}
            </strong>
        </li>
        <li><em>Billing Address:</em><br> {!! \Wax\Data::formatAddress($payment) !!}</li>
        <li>
            @if (!empty($payment->captured_at))
                <em>Captured At:</em> {{ $payment->captured_at }}
            @else
                <em>Authorized At:</em> {{ $payment->authorized_at_at }}
            @endif
        </li>
        <li><em>Auth Code:</em> {{ $payment->auth_code }}</li>
        <li><em>Ref:</em> {{ json_decode($payment->transaction_ref, true)['transId'] }}</li>
    </ul>
</div>
