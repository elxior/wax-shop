<ul>
    <li>Subtotal: {{ Currency::format($shipment->item_gross_subtotal) }}</li>

    @if (!empty($shipment->shipping_service_name))
        <li>
            {{ $shipment->shipping_carrier }} {{ $shipment->shipping_service_name }}:
            {{ Currency::format($shipment->shipping_gross_subtotal) }}
        </li>
    @endif

    @if (!empty($shipment->tracking_number))
        <li>Tracking Number: {{ $shipment->tracking_number }}</li>
    @endif

    @if ($shipment->tax_amount > 0)
        <li>Tax ({{ $shipment->tax_desc }}}): {{ Currency::format($shipment->tax_amount) }}</li>
    @endif
</ul>
