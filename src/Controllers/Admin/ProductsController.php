<?php namespace Sanatorium\Shop\Controllers\Admin;

use Platform\Access\Controllers\AdminController;
use Sanatorium\Shop\Repositories\Product\ProductRepositoryInterface;

class ProductsController extends AdminController {
 
	/**
	 * {@inheritDoc}
	 */
	protected $csrfWhitelist = [
		'executeAction',
	];

	/**
	 * The Shop repository.
	 *
	 * @var \Sanatorium\Shop\Repositories\Product\ProductRepositoryInterface
	 */
	protected $products;

	/**
	 * Holds all the mass actions we can execute.
	 *
	 * @var array
	 */
	protected $actions = [
		'delete',
		'enable',
		'disable',
	];

	/**
	 * Constructor.
	 *
	 * @param  \Sanatorium\Shop\Repositories\Product\ProductRepositoryInterface  $products
	 * @return void
	 */
	public function __construct(ProductRepositoryInterface $products)
	{
		parent::__construct();

		$this->products = $products;
	}

	/**
	 * Display a listing of product.
	 *
	 * @return \Illuminate\View\View
	 */
	public function index()
	{
		return view('sanatorium/shop::products.index');
	}

	/**
	 * Datasource for the product Data Grid.
	 *
	 * @return \Cartalyst\DataGrid\DataGrid
	 */
	public function grid()
	{
		$data = $this->products->grid();

		$columns = [
			'id',
			'slug',
			'code',
			'ean',
			'weight',
			'stock',
			'created_at',
		];

		$settings = [
			'sort'      => 'created_at',
			'direction' => 'desc',
		];

		$transformer = function($element)
		{
			$element->edit_uri = route('admin.sanatorium.shop.products.edit', $element->id);

			$element->imgurl = $element->coverThumb();

			return $element;
		};

		return datagrid($data, $columns, $settings, $transformer);
	}

	/**
	 * Show the form for creating new product.
	 *
	 * @return \Illuminate\View\View
	 */
	public function create()
	{
		return $this->showForm('create');
	}

	/**
	 * Handle posting of the form for creating new product.
	 *
	 * @return \Illuminate\Http\RedirectResponse
	 */
	public function store()
	{
		return $this->processForm('create');
	}

	/**
	 * Show the form for updating product.
	 *
	 * @param  int  $id
	 * @return mixed
	 */
	public function edit($id)
	{
		return $this->showForm('update', $id);
	}

	/**
	 * Handle posting of the form for updating product.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\RedirectResponse
	 */
	public function update($id)
	{
		return $this->processForm('update', $id);
	}

	/**
	 * Remove the specified product.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\RedirectResponse
	 */
	public function delete($id)
	{
		$type = $this->products->delete($id) ? 'success' : 'error';

		$this->alerts->{$type}(
			trans("sanatorium/shop::products/message.{$type}.delete")
		);

		return redirect()->route('admin.sanatorium.shop.products.all');
	}

	/**
	 * Executes the mass action.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function executeAction()
	{
		$action = request()->input('action');

		if (in_array($action, $this->actions))
		{
			foreach (request()->input('rows', []) as $row)
			{
				$this->products->{$action}($row);
			}

			return response('Success');
		}

		return response('Failed', 500);
	}

	/**
	 * Shows the form.
	 *
	 * @param  string  $mode
	 * @param  int  $id
	 * @return mixed
	 */
	protected function showForm($mode, $id = null)
	{
		// Do we have a product identifier?
		if (isset($id))
		{
			if ( ! $product = $this->products->find($id))
			{
				$this->alerts->error(trans('sanatorium/shop::products/message.not_found', compact('id')));

				return redirect()->route('admin.sanatorium.shop.products.all');
			}
		}
		else
		{
			$product = $this->products->createModel();
		}

		// Show the page
		return view('sanatorium/shop::products.form', compact('mode', 'product'));
	}

	/**
	 * Processes the form.
	 *
	 * @param  string  $mode
	 * @param  int  $id
	 * @return \Illuminate\Http\RedirectResponse
	 */
	protected function processForm($mode, $id = null)
	{
		// Store the product
		list($messages) = $this->products->store($id, request()->all());

		// Do we have any errors?
		if ($messages->isEmpty())
		{
			$this->alerts->success(trans("sanatorium/shop::products/message.success.{$mode}"));

			return redirect()->route('admin.sanatorium.shop.products.all');
		}

		$this->alerts->error($messages, 'form');

		return redirect()->back()->withInput();
	}

}
