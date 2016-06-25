@section('scripts')
    @parent
    <script type="text/javascript">
        $(function(){
            $('[data-order]').click(function(){

                window.location.href = '{{ strtok($_SERVER['REQUEST_URI'], '?') }}?order=' + $(this).data('order');
            });
        });
    </script>
@stop

<?php
//
$orders = [
        'name:asc' => trans('sanatorium/shop::general.sort.name:asc'),
        'name:desc' => trans('sanatorium/shop::general.sort.name:desc'),
        'price:asc' => trans('sanatorium/shop::general.sort.price:asc'),
        'price:desc' => trans('sanatorium/shop::general.sort.price:desc'),
];
?>

<div class="{{ $css_class }}">
    <button type="button" class="{{ $css_class_btn }} dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        {{ trans('sanatorium/shop::general.sort.sort_by') }}{{ Input::has('order') ? $orders[Input::get('order')] ? ': ' . $orders[Input::get('order')] : '' : '' }} <span class="caret"></span>
    </button>
    <ul class="dropdown-menu">
        @foreach($orders as $key => $name)
        <li class="{{ Input::get('order') == $key ? 'active' : null }}"><a href="#" data-order="{{ $key }}">{{ $name }}</a></li>
        @endforeach
    </ul>
</div>
