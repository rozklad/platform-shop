<?php namespace Sanatorium\Shop\Repositories\Product;

interface ProductRepositoryInterface {

	/**
	 * Returns a dataset compatible with data grid.
	 *
	 * @return \Sanatorium\Shop\Models\Product
	 */
	public function grid();

	/**
	 * Returns all the shop entries.
	 *
	 * @return \Sanatorium\Shop\Models\Product
	 */
	public function findAll();

	/**
	 * Returns a shop entry by its primary key.
	 *
	 * @param  int  $id
	 * @return \Sanatorium\Shop\Models\Product
	 */
	public function find($id);

	/**
	 * Determines if the given shop is valid for creation.
	 *
	 * @param  array  $data
	 * @return \Illuminate\Support\MessageBag
	 */
	public function validForCreation(array $data);

	/**
	 * Determines if the given shop is valid for update.
	 *
	 * @param  int  $id
	 * @param  array  $data
	 * @return \Illuminate\Support\MessageBag
	 */
	public function validForUpdate($id, array $data);

	/**
	 * Creates or updates the given shop.
	 *
	 * @param  int  $id
	 * @param  array  $input
	 * @return bool|array
	 */
	public function store($id, array $input);

	/**
	 * Creates a shop entry with the given data.
	 *
	 * @param  array  $data
	 * @return \Sanatorium\Shop\Models\Product
	 */
	public function create(array $data);

	/**
	 * Updates the shop entry with the given data.
	 *
	 * @param  int  $id
	 * @param  array  $data
	 * @return \Sanatorium\Shop\Models\Product
	 */
	public function update($id, array $data);

	/**
	 * Deletes the shop entry.
	 *
	 * @param  int  $id
	 * @return bool
	 */
	public function delete($id);

}
