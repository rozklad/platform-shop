<?php namespace Sanatorium\Shop\Providers;

use Cartalyst\Support\ServiceProvider;
use Illuminate\Foundation\AliasLoader;

class ProductServiceProvider extends ServiceProvider {

	/**
	 * {@inheritDoc}
	 */
	public function boot()
	{
		// Register the attributes namespace
		$this->app['platform.attributes.manager']->registerNamespace(
			$this->app['Sanatorium\Shop\Models\Product']
		);

		// Subscribe the registered event handler
		$this->app['events']->subscribe('sanatorium.shop.product.handler.event');

		// Register product as product
        AliasLoader::getInstance()->alias('Product', 'Sanatorium\Shop\Models\Product');  

        $this->registerCviebrockEloquentSluggablePackage();

        $this->prepareResources();
        
        //$this->registerIlluminateHtml();
	}

	/**
	 * {@inheritDoc}
	 */
	public function register()
	{
		// Register the repository
		$this->bindIf('sanatorium.shop.product', 'Sanatorium\Shop\Repositories\Product\ProductRepository');

		// Register the data handler
		$this->bindIf('sanatorium.shop.product.handler.data', 'Sanatorium\Shop\Handlers\Product\ProductDataHandler');

		// Register the event handler
		$this->bindIf('sanatorium.shop.product.handler.event', 'Sanatorium\Shop\Handlers\Product\ProductEventHandler');

		// Register the validator
		$this->bindIf('sanatorium.shop.product.validator', 'Sanatorium\Shop\Validator\Product\ProductValidator');

	}

	/**
     * Prepare the package resources.
     *
     * @return void
     */
    protected function prepareResources()
    {
        $config = realpath(__DIR__.'/../../config/config.php');

        $this->mergeConfigFrom($config, 'sanatorium-shop');

        $this->publishes([
            $config => config_path('sanatorium-shop.php'),
        ], 'config');
    }

	/**
	 * Register the cviebrock/eloquent-sluggable package.
	 * @return
	 */
	protected function registerCviebrockEloquentSluggablePackage() {
		$serviceProvider = 'Cviebrock\EloquentSluggable\SluggableServiceProvider';

		if (!$this->app->getProvider($serviceProvider)) {
			$this->app->register($serviceProvider);
		}
	}

	/**
	 * Register illuminate/html package
	 * @return
	 */
	protected function registerIlluminateHtml() {
		$serviceProvider = 'Illuminate\Html\HtmlServiceProvider';

		if (!$this->app->getProvider($serviceProvider)) {
			$this->app->register($serviceProvider);
			AliasLoader::getInstance()->alias('Form', 'Illuminate\Html\FormFacade');
			AliasLoader::getInstance()->alias('HTML', 'Illuminate\Html\HtmlFacade');
		}
	}
}
