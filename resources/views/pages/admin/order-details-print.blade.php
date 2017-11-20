@extends('admin::layouts.print')

@section('head')
    <!--
        NOTE: I'm doing this embedded style as a temporary hack. I expect a fed will want to rip this out
        and overhaul the css and probably the markup.
    -->
    <style type="text/css">
        section {
            margin-top: 30px;
            margin-bottom: 10px;
        }
        #cart-summary thead th {
            text-align: left;
        }
        ul {
            list-style-type: none;
        }
    </style>
    <script>
        window.onload = function () {
            window.print();
            setTimeout(function(){window.close();}, 1);
        }
    </script>
@endsection

@section('body')
    @include ('shop::components.admin.order-details.header', ['order' => $order])

    @foreach ($order->shipments as $shipment)
        <section>
            <h3>Shipment {{ $loop->iteration }}</h3>
            <p>
                <strong>Ship To:</strong><br>
                {!! \Wax\Data::formatAddress($shipment, true) !!}
            </p>
            @include ('shop::components.admin.order-details.cart', ['shipment' => $shipment])
            @include ('shop::components.admin.order-details.shipment-details-print', ['shipment' => $shipment])
        </section>
    @endforeach

    @include ('shop::components.admin.order-details.totals', ['order' => $order])

    @include ('shop::components.admin.order-details.payment-details', ['order' => $order])
@stop
