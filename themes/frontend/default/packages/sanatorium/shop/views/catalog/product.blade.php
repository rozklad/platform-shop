<!-- sanatorium/shop::catalog/product -->
<div class="col-sm-{{ ($cols/$per_row) }} text-center catalog-product">

	<div class="product-block" itemscope itemtype="http://schema.org/Product">

		<div class="thumb-area catalog-thumb-area">

			@if ( $product->hasCoverImage() )
			<a href="{{ $product->url }}" class="product-catalog-image">

				<img src="{{ $product->coverThumb() }}" itemprop="image" alt="{{ $product->product_title }}">

			</a>
			@endif

		</div>

		<h3 class="product-title" itemprop="name">
			<a href="{{ $product->url }}">
				{{ $product->product_title }}
			</a>
		</h3>

		{{-- @todo: move to shoppricing --}}
		<div class="price product-common-price product-price-default">
			{{ trans('sanatorium/pricing::general.price.vat') }} {{ $product->price }}
		</div>

		<div class="price product-common-price product-price-default-vat">
			{{ trans('sanatorium/pricing::general.price.no_vat') }} {{ $product->price_vat }}
		</div>

		@hook('catalog.product.bottom', $product)

	</div>

</div>
