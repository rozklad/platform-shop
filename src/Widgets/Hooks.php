<?php namespace Sanatorium\Shop\Widgets;

use Product;

class Hooks {

    public function search($term = null)
    {
        // If anything else was passed down to $term, let's filter it out
        if ( !is_string($term) )
            $term = null;

        if ( !isset($per_page) )
            $per_page = config('sanatorium-shop.per_page');

        $products = Product::ordered()
            ->search()
            ->take($per_page)
            ->orderBy('id', 'DESC')
            ->get();

        return view('sanatorium/shop::widgets/search', compact('products', 'per_page', 'term'));
    }

}
