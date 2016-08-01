<?php namespace Sanatorium\Shop\Handlers\Product;

use Illuminate\Events\Dispatcher;
use Sanatorium\Shop\Models\Product;
use Cartalyst\Support\Handlers\EventHandler as BaseEventHandler;
use Event;

class ProductEventHandler extends BaseEventHandler implements ProductEventHandlerInterface {

	/**
	 * {@inheritDoc}
	 */
	public function subscribe(Dispatcher $dispatcher)
	{
		$dispatcher->listen('sanatorium.shop.product.creating', __CLASS__.'@creating');
		$dispatcher->listen('sanatorium.shop.product.created', __CLASS__.'@created');

		$dispatcher->listen('sanatorium.shop.product.updating', __CLASS__.'@updating');
		$dispatcher->listen('sanatorium.shop.product.updated', __CLASS__.'@updated');

		$dispatcher->listen('sanatorium.shop.product.deleted', __CLASS__.'@deleted');
	}

	/**
	 * {@inheritDoc}
	 */
	public function creating(array $data)
	{

	}

	/**
	 * {@inheritDoc}
	 */
	public function created(Product $product)
	{
		$this->flushCache($product);

		//$this->refreshLists();
	}

	/**
	 * {@inheritDoc}
	 */
	public function updating(Product $product, array $data)
	{

	}

	/**
	 * {@inheritDoc}
	 */
	public function updated(Product $product)
	{
		$this->flushCache($product);

		$this->refreshLists();
	}

	/**
	 * {@inheritDoc}
	 */
	public function deleted(Product $product)
	{
		$this->flushCache($product);

		$this->refreshLists();
	}

	/**
	 * Flush the cache.
	 *
	 * @param  \Sanatorium\Shop\Models\Product  $product
	 * @return void
	 */
	protected function flushCache(Product $product)
	{
		$this->app['cache']->forget('sanatorium.shop.product.all');

		$this->app['cache']->forget('sanatorium.shop.product.'.$product->id);

        $this->app['cache']->forget('sanatorium.shop.redirects');
	}

	protected function refreshLists()
	{
		Event::fire('sanatorium.shop.product.lists.refresh');
	}

}
