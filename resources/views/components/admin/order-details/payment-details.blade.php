<section>
    <h3>Payment Details</h3>

    @foreach ($order->payments as $payment)
        @if ($payment->type == 'Credit Card')
            @include ('shop::components.admin.order-details.payment.credit-card', ['payment' => $payment])
        @else
            <?php throw new \Exception('unhandled payment type: ' . $payment->type) ?>
        @endif
    @endforeach
</section>
