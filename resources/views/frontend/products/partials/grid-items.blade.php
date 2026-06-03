@foreach($products as $product)
    @include('frontend.products.partials.card', ['product' => $product])
@endforeach
