<?php $sizes = ['xs', 'sm', 'md', 'lg']; ?>

@if ( !isset($per_row) )
	<?php $per_row = config('sanatorium-shop.per_row'); ?>
@endif

@if ( !isset($cols) )
	<?php $cols_total = config('sanatorium-shop.cols'); ?>
@endif

<!-- sanatorium/shop::catalog/product -->
<a class="col-sm-{{ ($cols_total/$per_row['default']) }}
		@foreach( $per_row as $size => $cols )

			@if ( isset($cols) )

				col-{{ $size }}-{{ ($cols_total/$cols) }}

			@endif

		@endforeach
		catalog-product product-block" href="{{ $product->url }}" itemscope itemtype="http://schema.org/Product">

	<div class="thumb-area catalog-thumb-area">
		
		{{-- Thumb picture coverThumb() accepts styles registered in platform-media.styles configuration (150|300|600 by default) --}}
		@if ( $product->hasCoverImage() )
			<img src="{{ $product->coverThumb(300) }}" itemprop="image" alt="{{ $product->product_title }}">
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
