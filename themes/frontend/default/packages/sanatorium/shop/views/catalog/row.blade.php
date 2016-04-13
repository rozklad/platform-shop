<?php
// Set how many products per row
if ( !isset($per_row) )
	$per_row = config('sanatorium-shop.products_per_row');

$i = 0;
?>

<div class="row catalog-row">
@foreach($products as $product)

	<?php $i++; ?>

	@include('sanatorium/shop::catalog/product')

	@if ( $i % 3 == 0)
		</div>
		<div class="row catalog-row">
	@endif

@endforeach
{{-- foreach product --}}
</div>