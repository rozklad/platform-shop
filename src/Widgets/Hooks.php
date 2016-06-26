<?php namespace Sanatorium\Shop\Widgets;

use Product;

class Hooks {

    public function search()
    {

        if ( !isset($per_page) ) {
            $per_page = config('sanatorium-shop.per_page');
        }

        $products = Product::ordered()
            ->search()
            ->take($per_page)
            ->orderBy('id', 'DESC')
            ->get();

        return view('sanatorium/shop::widgets/search', compact('products', 'per_page'));
    }

}
