@extends ('layouts.full-width')

@section('main')
    <div class="product-detail__crumbs-container">
        {!! Breadcrumbs::draw('components.breadcrumbs') !!}
    </div>
    <article class="product-detail">
        <div class="product-detail__col product-detail__col--image">
            <img class="product-detail__image" src="{{ $product['defaultImage']['image']['copy']['large']['url'] }}"
                 alt="{{ $product['defaultImage']['caption'] }}"/>
        </div>
        <div class="product-detail__col product-detail__col--content">
            <div class="product-detail__content">
                <h1 class="product-detail__page-title">
                    {{ $product['name'] }}
                </h1>
                <div class="product-detail__category">
                    {{ $product['category']['name'] }}
                </div>
                <div class="product-detail__short-desc wysiwyg">
                    {!! $product['description'] !!}
                </div>
            </div>
            <div class="product-detail__controls">
                <div class="product-detail__price">
                    ${{ number_format($product['price'], 2) }}
                </div>
                <a href="#" class="button" data-shop-id="{{$product['id']}}" data-shop-add>
                    Add to Cart
                </a>
            </div>
            @if ($product['bundles'])
                <section class="product-detail__special-offers">
                    <h2 class="product-detail__subheading">Special Offers</h2>
                    @foreach ($product['bundles'] as $bundle)
                        <section class="product-detail__bundle">
                            <h3 class="product-detail__callout">
                                {{ $bundle['name'] }}: Purchase these products together and
                                receive {{ $bundle['percent'] }}% off each.
                            </h3>
                            <ul class="product-detail__list">
                                @foreach ($bundle['products'] as $relatedProduct)
                                    <li class="product-detail__item"><a
                                                href="{{ $relatedProduct['url'] }}">{{ $relatedProduct['name'] }}</a>
                                    </li>
                                @endforeach
                            </ul>
                        </section>
                    @endforeach
                </section>
            @endif
        </div>
    </article>
@endsection
