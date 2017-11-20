<div class="order-items">
    <section>
        <h3>Shipment {{ $loop->iteration }}</h3>
        <p>
            <strong>Ship To:</strong><br>
            {!! \Wax\Data::formatAddress($shipment, true) !!}
        </p>

        <table class="cart-style account-order-details" id="cart-summary">
            <thead>
            <tr>
                <th>
                    SKU
                </th>
                <th>
                    Product Name
                </th>
                <th>
                    Quantity
                </th>
                <th>
                    Unit Price
                </th>
            </tr>
            </thead>
            <tbody>
            @foreach ($shipment->items as $item)
                <tr data-id="$item['id']">
                    <td class="cartitem-sku">
                        {{ $item->sku }}
                    </td>
                    <td class="cartitem-name">
                        {{ $item->name }}
                        @foreach ($item->options as $option)
                            {{ $option->name }}: {{ $option->value }}
                        @endforeach
                    </td>
                    <td class="cartitem-name">
                        {{ $item->quantity }}
                    </td>
                    <td class="cartitem-unit-price">
                        {{ Currency::format($item->gross_unit_price) }}
                    </td>
                </tr>
            @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td>
                        Subtotal:
                    </td>
                    <td>
                        {{ Currency::format($shipment->item_gross_subtotal) }}
                    </td>
                </tr>
                @if (!empty($shipment->shipping_service_name))
                    <tr>
                        <td>
                            {{ $shipment->shipping_carrier }} {{ $shipment->shipping_service_name }}
                            @if (!empty($shipment->tracking_number))
                                <br>{{ $shipment->tracking_number }}
                            @endif
                        </td>
                        <td>
                            {{ Currency::format($shipment->shipping_gross_subtotal) }}
                        </td>
                    </tr>
                @endif
                @if ($shipment->tax_amount > 0)
                    <tr>
                        <td>
                            Tax ({{ $shipment->tax_desc }}})
                        </td>
                        <td>
                            {{ Currency::format($shipment->tax_amount) }}
                        </td>
                    </tr>
                @endif
            </tfoot>
        </table>
    </section>
</div>
