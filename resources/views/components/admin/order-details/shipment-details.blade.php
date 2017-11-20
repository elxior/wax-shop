<ul>
    <li>Subtotal: {{ Currency::format($shipment->item_gross_subtotal) }}</li>

    @if (!empty($shipment->shipping_service_name))
        <li>
            {{ $shipment->shipping_carrier }} {{ $shipment->shipping_service_name }}:
            {{ Currency::format($shipment->shipping_gross_subtotal) }}
        </li>
    @endif

    @if ($shipment->tax_amount > 0)
        <li>Tax ({{ $shipment->tax_desc }}}): {{ Currency::format($shipment->tax_amount) }}</li>
    @endif

    <li>
        <form action="{{ route('shop::orderDetails.addTracking', [
                            'id' => $id,
                            'shipmentId' => $shipment->id
                        ]) }}" method="POST">
            {!! csrf_field() !!}
            Tracking number:
            <input type="text" name="tracking_number" value="{{ $shipment->tracking_number }}">
            <input type="submit" value="Save">
        </form>
    </li>
</ul>
