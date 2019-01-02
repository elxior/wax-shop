<section>
    <h3>Payment Details</h3>

    @foreach ($order->payments as $payment)
        @include ('shop::components.admin.order-details.payment.' . str_replace('_', '-', $payment->type), [
            'payment' => $payment
        ])
    @endforeach
</section>
