<?php namespace Sanatorium\Shop\Widgets;

class Sort {

    public function compact($css_class = 'btn-group', $css_class_btn = 'btn btn-default')
    {
        return view('sanatorium/shop::widgets/sort/compact', compact('css_class', 'css_class_btn'));
    }

}
