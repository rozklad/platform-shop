<?php namespace Sanatorium\Shop\Repositories\Product;

use Cartalyst\Support\Traits;
use Illuminate\Container\Container;
use Symfony\Component\Finder\Finder;

class ProductRepository implements ProductRepositoryInterface {

	use Traits\ContainerTrait, Traits\EventTrait, Traits\RepositoryTrait, Traits\ValidatorTrait;

	/**
	 * The Data handler.
	 *
	 * @var \Sanatorium\Shop\Handlers\Product\ProductDataHandlerInterface
	 */
	protected $data;

	/**
	 * The Eloquent shop model.
	 *
	 * @var string
	 */
	protected $model;

	/**
	 * Constructor.
	 *
	 * @param  \Illuminate\Container\Container  $app
	 * @return void
	 */
	public function __construct(Container $app)
	{
		$this->setContainer($app);

		$this->tags = $app['platform.tags'];

		$this->setDispatcher($app['events']);

		$this->data = $app['sanatorium.shop.product.handler.data'];

		$this->setValidator($app['sanatorium.shop.product.validator']);

		$this->setModel(get_class($app['Sanatorium\Shop\Models\Product']));
	}

	/**
	 * {@inheritDoc}
	 */
	public function grid()
	{
		return $this
			->createModel();
	}

	/**
	 * {@inheritDoc}
	 */
	public function findAll()
	{
		return $this->container['cache']->rememberForever('sanatorium.shop.product.all', function()
		{
			return $this->createModel()->get();
		});
	}

	/**
	 * {@inheritDoc}
	 */
	public function find($id)
	{
		return $this->container['cache']->rememberForever('sanatorium.shop.product.'.$id, function() use ($id)
		{
			return $this->createModel()->find($id);
		});
	}

	/**
	 * {@inheritDoc}
	 */
	public function validForCreation(array $input)
	{
		return $this->validator->on('create')->validate($input);
	}

	/**
	 * {@inheritDoc}
	 */
	public function validForUpdate($id, array $input)
	{
		return $this->validator->on('update')->validate($input);
	}

	/**
	 * {@inheritDoc}
	 */
	public function store($id, array $input)
	{
		return ! $id ? $this->create($input) : $this->update($id, $input);
	}

	/**
	 * {@inheritDoc}
	 */
	public function create(array $input)
	{
		// Create a new product
		$product = $this->createModel();

		// Fire the 'sanatorium.shop.product.creating' event
		if ($this->fireEvent('sanatorium.shop.product.creating', [ $input ]) === false)
		{
			return false;
		}

		// Get the submitted tags
        $tags = array_pull($input, 'tags', []);

        // Get the submitted categories
        $categories = array_pull($input, 'categories', []);

        // Get the submitted manufacturers
        $manufacturers = array_pull($input, 'manufacturers', []);

		// Prepare the submitted data
		$data = $this->data->prepare($input);

		// Validate the submitted data
		$messages = $this->validForCreation($data);

		// Check if the validation returned any errors
		if ($messages->isEmpty())
		{
			// Save the product
			// Resluggify
			if ( method_exists($product, 'resluggify') )
				$product->fill($data)->resluggify()->save();
			else
				$product->fill($data)->save();

			// Set the tags on the page entry
            $this->tags->set($product, $tags);

            // Set categories
            $product->categories()->sync($categories);

            // Set manufacturers
            $product->manufacturers()->sync($manufacturers);

			// Fire the 'sanatorium.shop.product.created' event
			$this->fireEvent('sanatorium.shop.product.created', [ $product ]);
		}

		return [ $messages, $product ];
	}

	/**
	 * {@inheritDoc}
	 */
	public function update($id, array $input)
	{
		// Get the product object
		$product = $this->find($id);

		// Fire the 'sanatorium.shop.product.updating' event
		if ($this->fireEvent('sanatorium.shop.product.updating', [ $product, $input ]) === false)
		{
			return false;
		}

		// Get the submitted tags
        $tags = array_pull($input, 'tags', []);

        // Get the submitted categories
        $categories = array_pull($input, 'categories', []);

        // Get the submitted manufacturers
        $manufacturers = array_pull($input, 'manufacturers', []);

		// Prepare the submitted data
		$data = $this->data->prepare($input);

		// Validate the submitted data
		$messages = $this->validForUpdate($product, $data);

		// Check if the validation returned any errors
		if ($messages->isEmpty())
		{
			// Update the product
			// Resluggify
			if ( method_exists($product, 'resluggify') )
				$product->fill($data)->resluggify()->save();
			else
				$product->fill($data)->save();

			// Set the tags on the page entry
            $this->tags->set($product, $tags);

            // Set categories
            $product->categories()->sync($categories);

            // Set manufacturers
            $product->manufacturers()->sync($manufacturers);

			// Fire the 'sanatorium.shop.product.updated' event
			$this->fireEvent('sanatorium.shop.product.updated', [ $product ]);
		}

		return [ $messages, $product ];
	}

	/**
	 * {@inheritDoc}
	 */
	public function delete($id)
	{
		// Check if the product exists
		if ($product = $this->find($id))
		{
			// Fire the 'sanatorium.shop.product.deleted' event
			$this->fireEvent('sanatorium.shop.product.deleted', [ $product ]);

			// Delete the product entry
			$product->delete();

			return true;
		}

		return false;
	}

	/**
	 * {@inheritDoc}
	 */
	public function enable($id)
	{
		$this->validator->bypass();

		return $this->update($id, [ 'enabled' => true ]);
	}

	/**
	 * {@inheritDoc}
	 */
	public function disable($id)
	{
		$this->validator->bypass();

		return $this->update($id, [ 'enabled' => false ]);
	}

}
