<?php namespace Sanatorium\Shop\Database\Seeds;

use DB;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;

class ProductattributesTableSeeder extends Seeder {

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		self::seedAttributes();
	}

	public static function seedAttributes()
	{
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
				'namespace'   => \Sanatorium\Shop\Models\Product::getEntityNamespace(),
				'name'        => $attribute['name'],
				'description' => $attribute['description'],
				'type'        => $attribute['type'],
				'slug'        => $attribute['slug'],
				'enabled'     => 1,
			]);
		}
	}

}
