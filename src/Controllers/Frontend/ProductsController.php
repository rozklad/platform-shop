<?php namespace Sanatorium\Shop\Controllers\Frontend;

use Platform\Foundation\Controllers\Controller;
use Product;

class ProductsController extends Controller {

	/**
	 * Return the main view.
	 *
	 * @return \Illuminate\View\View
	 */
	public function index()
	{
		return view('sanatorium/shop::index');
	}

	public function productBySlug($slug)
	{
		$product = Product::where('slug', $slug)->first();

		return view('sanatorium/shop::products/view', compact('product'));
	}

}
