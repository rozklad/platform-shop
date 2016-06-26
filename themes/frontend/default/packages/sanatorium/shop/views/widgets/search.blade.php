

{{-- Products search results --}}

@if ( count($products) )
    @include('sanatorium/shop::catalog/row')
@else
    {!! trans('sanatorium/search::general.no_search_results', ['term' => $term]) !!}
@endif