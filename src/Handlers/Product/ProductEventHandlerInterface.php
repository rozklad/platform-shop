<?php namespace Sanatorium\Shop\Handlers\Product;

use Sanatorium\Shop\Models\Product;
use Cartalyst\Support\Handlers\EventHandlerInterface as BaseEventHandlerInterface;

interface ProductEventHandlerInterface extends BaseEventHandlerInterface {

	/**
	 * When a product is being created.
	 *
	 * @param  array  $data
	 * @return mixed
	 */
	public function creating(array $data);

	/**
	 * When a product is created.
	 *
	 * @param  \Sanatorium\Shop\Models\Product  $product
	 * @return mixed
	 */
	public function created(Product $product);

	/**
	 * When a product is being updated.
	 *
	 * @param  \Sanatorium\Shop\Models\Product  $product
	 * @param  array  $data
	 * @return mixed
	 */
	public function updating(Product $product, array $data);

	/**
	 * When a product is updated.
	 *
	 * @param  \Sanatorium\Shop\Models\Product  $product
	 * @return mixed
	 */
	public function updated(Product $product);

	/**
	 * When a product is deleted.
	 *
	 * @param  \Sanatorium\Shop\Models\Product  $product
	 * @return mixed
	 */
	public function deleted(Product $product);

}
