<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('shop_products', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('slug');
			$table->string('code')->nullable();
			$table->string('ean')->nullable();
			$table->integer('weight')->default('0');
			$table->integer('stock')->default('0');
			$table->timestamps();
		});

		$attributesRepo = app('Platform\Attributes\Repositories\AttributeRepositoryInterface');

		$attributes = [
			[
				'name' => 'Product title',
				'type' => 'input',
				'description' => 'Product title',
				'slug' => 'product_title',
			],
			[
				'name' => 'Product description',
				'type' => 'wysiwyg',
				'description' => 'Product description',
				'slug' => 'product_description',
			],
			[
				'name' => 'Product code',
				'type' => 'input',
				'description' => 'Product code',
				'slug' => 'product_code',
			],
			[
				'name' => 'Product cover',
				'type' => 'image',
				'description' => 'Product cover',
				'slug' => 'product_cover',
			],
			[
				'name' => 'Product gallery',
				'type' => 'gallery',
				'description' => 'Product gallery',
				'slug' => 'product_gallery',
			]
		];


		foreach( $attributes as $attribute )
		{
			$attributesRepo->firstOrCreate([
				'namespace'   => Sanatorium\Shop\Models\Product::getEntityNamespace(),
				'name'        => $attribute['name'],
				'description' => $attribute['description'],
				'type'        => $attribute['type'],
				'slug'        => $attribute['slug'],
				'enabled'     => 1,
			]);
		}
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('products');
	}

}
