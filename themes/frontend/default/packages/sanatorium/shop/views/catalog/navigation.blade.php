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

<div class="navbar navbar-default catalog-navigation">
	<div class="container-fluid">
		<div class="navbar-left navbar-text pagination-text">
			{!! trans('sanatorium/shop::catalog/pagination.info', [
				'currentPage' => $products->currentPage(),
				'lastPage' => $products->lastPage(),
				'perPage' => $products->perPage(),
				'total' => $products->total(),
				'from_num' => $products->firstItem(),
				'to_num' => $products->lastItem(),
				'count' => $products->count(),
				]) !!}
		</div>

		@include('sanatorium/shop::catalog/pagination', ['paginator' => $products, 'class' => 'navbar-nav navbar-right'])
	</div>
</div>