<section>
    <h3>Order #{{$order->sequence}}</h3>
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
</section>
