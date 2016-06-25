@section('styles')
@parent
<style type="text/css">
.catalog-navigation .nav.navbar-nav .active span {
	padding-top: 15px;
    padding-bottom: 15px;
    padding-left: 10px;
    padding-right: 10px;
    line-height: 20px;
    display: block;
}
</style>
@stop

@section('scripts')
@parent
<script type="text/javascript">
$(function(){
	$('[name="order"], [name="per_page"]').change(function(){
		$(this).parents('form:first').submit();
	});
});
</script>
@stop

<?php
$orders = [
		'name:asc' => trans('sanatorium/shop::general.sort.name:asc'),
		'name:desc' => trans('sanatorium/shop::general.sort.name:desc'),
		'price:asc' => trans('sanatorium/shop::general.sort.price:asc'),
		'price:desc' => trans('sanatorium/shop::general.sort.price:desc'),
];

$default_per_page = config('sanatorium-shop.per_page');

// Value => Label
$per_pages = [
	$default_per_page => $default_per_page,
	$default_per_page*2 => $default_per_page*2,
	$default_per_page*3 => $default_per_page*3
];
?>

<div class="navbar catalog-navigation">
	<form class="navbar-form navbar-left" action="{{ strtok($_SERVER['REQUEST_URI'], '?') }}" role="search" method="GET">
		<div class="form-group">
			<label class="control-label" for="order_choose">
				{{ trans('pagination.order.label') }}
			</label>
			<select name="order" class="form-control" id="order_choose">
				@foreach($orders as $key => $name)
				<option value="{{ $key }}" {{ Input::get('order') == $key ? 'selected' : null }}>{{ $name }}</option>
				@endforeach
			</select>

			{{-- Transfer other query params --}}
			@foreach(request()->except(['order']) as $key => $value)
				<input type="hidden" name="{{ $key }}" value="{{ $value }}">
			@endforeach
		</div>
	</form>

	<form class="navbar-form navbar-left" action="{{ strtok($_SERVER['REQUEST_URI'], '?') }}" role="search" method="GET">
		<div class="form-group">
			<label class="control-label" for="per_page_choose">
				{{ trans('pagination.per_page.label') }}
			</label>
			<select name="per_page" class="form-control" id="per_page_choose">
				@foreach($per_pages as $key => $name)
				<option value="{{ $key }}" {{ Input::get('per_page') == $key ? 'selected' : null }}>{{ $name }}</option>
				@endforeach
			</select>

			{{-- Transfer other query params --}}
			@foreach(request()->except(['per_page']) as $key => $value)
				<input type="hidden" name="{{ $key }}" value="{{ $value }}">
			@endforeach
		</div>
	</form>

	@include('sanatorium/shop::catalog/pagination', ['paginator' => $products, 'class' => 'navbar-nav navbar-right'])
</div>