@extends('site.layouts.base', [
    'pageTitle' => 'Shop'
])

@section('body')
    <div class="container">
        <div class="-mb-lg">
            {!! Breadcrumbs::draw() !!}
        </div>
        <h1 class="page-title">Shop</h1>
        <h2 class="-mb-sm">Featured</h2>
        <ul class="-mb-lg">
            @foreach ($featuredProducts as $product)
                <li class="-mb-sm">
                    <h3><a href="{{ $product['url'] }}">{{ $product['name'] }}</a></h3>
                    <p class="-fz-xs"><a href="{{ $product['category']['url'] }}">{{ $product['category']['name'] }}</a></p>
                    <p>{!! $product['short_description'] !!}</p>
                    <p>{{ $product['price'] }}</p>
                </li>   
            @endforeach
        </ul>
        <h2 class="-mb-sm">Catalog</h2>
        <ul>
            @foreach ($nonFeaturedProducts as $product)
                <li class="-mb-sm">
                    <h3><a href="{{ $product['url'] }}">{{ $product['name'] }}</a></h3>
                    <p class="-fz-xs"><a href="{{ $product['category']['url'] }}">{{ $product['category']['name'] }}</a></p>
                    <p>{!! $product['short_description'] !!}</p>
                    <p>{{ $product['price'] }}</p>
                </li>   
            @endforeach
        </ul>
    </div>
@endsection