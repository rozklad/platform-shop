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

		// Seed attributes
		Sanatorium\Shop\Database\Seeds\ProductattributesTableSeeder::seedAttributes();
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
