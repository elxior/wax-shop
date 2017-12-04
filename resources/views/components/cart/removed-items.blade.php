<ul>
    @foreach($items as $name => $quantity)
        <li>{{ $name }} ({{ $quantity }})</li>
    @endforeach
</ul>