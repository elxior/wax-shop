@extends('site.layouts.base')

@section('body')
    <div class="container">
        {!! Breadcrumbs::draw() !!}
        <form class="product-detail" data-component="ajax-form" action="/shop/api/cart">
            <div class="product-detail__col product-detail__col--image">
                <img class="product-detail__image" src="{{ $product['defaultImage']['image']['copy']['large']['url'] }}"
                     alt="{{ $product['defaultImage']['caption'] }}"/>
            </div>
            <div class="product-detail__col product-detail__col--content">
                <div class="product-detail__content">
                    <h1 class="page-title product-detail__page-title">
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
                    <div class="product-detail__price -mb-sm">
                        ${{ number_format($product['price'], 2) }}
                    </div>
                    @include('forms.components.standard-select', [
                        'input' => [
                            'name' => 'quantity',
                            'label' => 'Quantity',
                            'options' => [
                                [
                                    'label' => '1',
                                    'value' => '1',
                                    'isDisabled' => false,
                                    'isSelected' => false,
                                ],
                                [
                                    'label' => '2',
                                    'value' => '2',
                                    'isDisabled' => false,
                                    'isSelected' => false,
                                ],
                                [
                                    'label' => '3',
                                    'value' => '3',
                                    'isDisabled' => false,
                                    'isSelected' => false,
                                ],
                                [
                                    'label' => '4',
                                    'value' => '4',
                                    'isDisabled' => false,
                                    'isSelected' => false,
                                ],
                                [
                                    'label' => '5',
                                    'value' => '5',
                                    'isDisabled' => false,
                                    'isSelected' => false,
                                ],
                                [
                                    'label' => '6',
                                    'value' => '6',
                                    'isDisabled' => false,
                                    'isSelected' => false,
                                ],
                                [
                                    'label' => '7',
                                    'value' => '7',
                                    'isDisabled' => false,
                                    'isSelected' => false,
                                ],
                                [
                                    'label' => '8',
                                    'value' => '8',
                                    'isDisabled' => false,
                                    'isSelected' => false,
                                ],
                                [
                                    'label' => '9',
                                    'value' => '9',
                                    'isDisabled' => false,
                                    'isSelected' => false,
                                ],
                                [
                                    'label' => '10',
                                    'value' => '10',
                                    'isDisabled' => false,
                                    'isSelected' => false,
                                ]
                            ]
                        ]
                    ])
                    <input type="hidden" name="product_id" value="{{ $product['id'] }}">
                    <button type="submit" data-element="submit">
                        Add to Cart
                    </button>
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
        </form>
    </div>
@endsection
