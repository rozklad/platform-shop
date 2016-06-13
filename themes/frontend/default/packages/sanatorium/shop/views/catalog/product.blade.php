<?php $sizes = ['xs', 'sm', 'md', 'lg']; ?>

@if ( !isset($per_row) )
	<?php $per_row = config('sanatorium-shop.per_row'); ?>
@endif

<!-- sanatorium/shop::catalog/product -->
<a class="col-sm-{{ ($cols/$per_row) }}
		@if ( isset($sizes) )
			@foreach( $sizes as $size )

				<?php $size_name = 'per_row_' . $size; ?>
				@if ( isset(${$size_name}) )

					col-{{ $size }}-{{ ($cols/${$size_name}) }}

				@endif

			@endforeach
		@endif
		catalog-product product-block" href="{{ $product->url }}" itemscope itemtype="http://schema.org/Product">

	<div class="thumb-area catalog-thumb-area">

		@if ( $product->hasCoverImage() )
			<img src="{{ $product->coverThumb() }}" itemprop="image" alt="{{ $product->product_title }}">
		@endif

	</div>

	<h3 class="product-title" itemprop="name">
		{{ $product->product_title }}
	</h3>

	{{-- @todo: move to shoppricing --}}
	<div class="price product-common-price product-price-default">
		{{ trans('sanatorium/pricing::general.price.vat') }} {{ $product->price }}
	</div>

	<div class="price product-common-price product-price-default-vat">
		{{ trans('sanatorium/pricing::general.price.no_vat') }} {{ $product->price_vat }}
	</div>

	@hook('catalog.product.bottom', $product)

</a>
