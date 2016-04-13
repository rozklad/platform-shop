@if ($paginator->lastPage() > 1)
    
    <?php
        // Configuration
        $range = 2;
        $start = $paginator->currentPage() - $range;    // show $range pagination links before current
        $end = $paginator->currentPage() + $range;      // show $range pagination links after current
        if($start < 1) $start = 1;                      // reset start to 1
        if($end >= $paginator->lastPage() ) $end = $paginator->lastPage(); // reset end to last page
    ?>

    <ul class="pagination catalog-pagination {{ (isset($class) ? $class : '') }}">
        
        @if ($paginator->currentPage() != 1)
        <li>
            <a class="{{ ($paginator->currentPage() == 1) ? ' disabled' : '' }}" href="{{ $paginator->url(1) }}">
                <i class="fa fa-angle-left"></i>
                {{ trans('pagination.previous_short') }}
            </a>
        </li>
        @endif

        @if($start>1)
            <li>
                <a href="{{ $paginator->url(1) }}">{{1}}</a>
            </li>

            <li>
                <span>
                    &hellip;
                </span>
            </li>
        @endif

        @for ($i = $start; $i <= $end; $i++)
            <li  class="{{ ($paginator->currentPage() == $i) ? ' active' : '' }}">
                <a href="{{ $paginator->url($i) }}">
                    {{$i}}
                </a>
            </li>
        @endfor

        @if($end<$paginator->lastPage())
            <li>
                <span>
                    &hellip;
                </span>
            </li>
            
            <li>
                <a href="{{ $paginator->url($paginator->lastPage()) }}">
                    {{$paginator->lastPage()}}
                </a>
            </li>
        @endif
        
        @if ($paginator->currentPage() != $paginator->lastPage())
        <li>
            <a class="{{ ($paginator->currentPage() == $paginator->lastPage()) ? ' disabled' : '' }}" href="{{ $paginator->url($paginator->currentPage()+1) }}">
                {{ trans('pagination.next_short') }}
                <i class="fa fa-angle-right"></i>
            </a>
        </li>
        @endif

    </ul>
@endif