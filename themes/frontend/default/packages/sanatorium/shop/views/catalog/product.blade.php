@section('styles')
@parent
<style type="text/css">
.thumb-area {
	display: inline-block;
	max-width: 200px;
	max-height: 200px;
}
</style>
@stop

<div class="col-sm-{{ ($per_page/$cols) }} text-center">	

	<div class="product-block" itemscope itemtype="http://schema.org/Product">

	<h3 class="product-title" itemprop="name">
		<a href="{{ $product->url }}">	
			{{ $product->product_title }}
		</a>
	</h3>

	<a href="{{ $product->url }}" class="thumb-area product-image">

	
		<img src="{{ $product->coverThumb(200,200) }}" itemprop="image" alt="{{ $product->product_title }}">

	</a>

	<div class="price product-common-price product-price-default">
		Cena bez DPH: {{ $product->price }}
	</div>

	<div class="price product-common-price product-price-default-vat">
		Cena s DPH: {{ $product->price_vat }}
	</div>

	@hook('catalog.product.bottom', $product)

	</div>

</div>
