@extends('site.layouts.base', [
    'pageTitle' => 'Cart'
])

@section('body')
	<div class="container">
        <div class="-mb-lg">
            {!! Breadcrumbs::draw() !!}
        </div>
        <h1 class="page-title">Cart</h1>
    </div>
@endsection
