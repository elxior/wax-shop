<thead>
    @foreach ($options->first()->values as $value)
        <th>{{ $value->option_name }}</th>
    @endforeach
    <th>SKU</th>
    <th>Price</th>
    <th>Weight</th>

    <th>Inventory<br /><em>Tracking: {{ config('wax.shop.inventory.track') ? 'On' : 'Off' }}</em></th>
    <th>Disable</th>
</thead>