<table class="cart-style account-order-details" id="cart-summary">
    <thead>
    <tr>
        <th>
            Product Name
        </th>
        <th>
            SKU
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
            <td class="cartitem-name">
                {{ $item->name }}
                @foreach ($item->options as $option)
                    {{ $option->name }}: {{ $option->value }}
                @endforeach
            </td>
            <td class="cartitem-sku">
                {{ $item->sku }}
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
</table>
