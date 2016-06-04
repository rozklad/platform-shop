<?php namespace Sanatorium\Shop\Models;

use DB;
use Cartalyst\Attributes\EntityInterface;
use Illuminate\Database\Eloquent\Model;
use Platform\Attributes\Traits\EntityTrait;
use Cartalyst\Support\Traits\NamespacedEntityTrait;
use Sanatorium\Pricing\Traits\PriceableTrait;
use Sanatorium\Categories\Traits\CategoryTrait;
use Sanatorium\Manufacturers\Traits\ManufacturerTrait;
use Cviebrock\EloquentSluggable\SluggableTrait;
use Sanatorium\Inputs\Traits\MediableTrait;
use Sanatorium\Thumbs\Traits\ThumbTrait;
use Cartalyst\Tags\TaggableTrait;
use Cartalyst\Tags\TaggableInterface;
use Sanatorium\Stock\Traits\StockTrait;

class Product extends Model implements EntityInterface, TaggableInterface {

	use EntityTrait,
		NamespacedEntityTrait,
		TaggableTrait,
		PriceableTrait,
		CategoryTrait,
		ManufacturerTrait,
		SluggableTrait,
		MediableTrait,
		//ThumbTrait,
		StockTrait;

	protected $sluggable = [
		'build_from' => 'product_title',
		'save_to'    => 'slug',
	];

	/**
	 * {@inheritDoc}
	 */
	protected $table = 'shop_products';

	/**
	 * {@inheritDoc}
	 */
	protected $guarded = [
		'id',
	];

	/**
	 * {@inheritDoc}
	 */
	protected $with = [
		'values.attribute',
	];

	/**
	 * {@inheritDoc}
	 */
	protected static $entityNamespace = 'sanatorium/shop.product';

	public function getUrlAttribute()
	{
		if ( class_exists('\Category') ) {
			// Categories are installed, return better optimized SEO url
			return route('sanatorium.categories.product.view', $this->slug);
		}

		return route('sanatorium.shop.products.view', $this->slug);
	}


	/**
	 * Search & Filter constants
	 */

	CONST ATTRIBUTES_TABLE = 'attributes';
	CONST ATTRIBUTE_VALUES_TABLE = 'attribute_values';

	public function scopeOrdered($query, $delimiter = ':')
	{
		if ( !request()->has('order') ) {
			return $query->orderBy('created_at', 'DESC');
		}

		$order = request()->get('order');

		// Add default order way if none is given
		if ( strpos($order, $delimiter) === false ) {
			$order = $order . $delimiter . 'ASC';
		}

		list($orderby, $orderway) = explode($delimiter, $order);

		switch( $orderby )
		{

			case 'price':
				return $query->join('priced', 'shop_products.id', '=', 'priced.priceable_id')
					->join('shop_money', 'priced.money_id', '=', 'shop_money.id')
					->where('shop_money.currency_id', 1)
					->select('shop_products.*')
					->orderBy( \DB::raw('CAST(shop_money.amount AS DECIMAL)'), $orderway);
				break;

			case 'name':
				return $query->join(self::ATTRIBUTE_VALUES_TABLE, 'shop_products.id', '=', self::ATTRIBUTE_VALUES_TABLE . '.entity_id')
					->join('attributes', 'attributes.id', '=', self::ATTRIBUTE_VALUES_TABLE . '.attribute_id')
					->where('attributes.slug', '=', 'product_title')
					->where('attributes.namespace', '=', $this->getEntityNamespace())
					->groupBy('shop_products.id')
					->select('shop_products.*')
					->orderBy(self::ATTRIBUTE_VALUES_TABLE . '.value', $orderway);
				break;
		}
	}


	/**
	 * Search
	 */

	public function scopeSearch($query, $term = null)
	{
		/* Get the search query */
		if (!$term)
			$term = request()->get(trans('sanatorium/shop::general.search.input'));

		if (!$term)
			return $query;

		/* Get attributes of Product */
		$attributes = self::getProductAttributes();

		/* Get related attributes of Product */
		$related = self::getProductRelatedAttributes();


		$search = [];

		// Get all products that contain search term
		// in its attributes
		foreach ( $attributes as $slug ) {
			if ( $term ) {
				$search[] = self::getFilteredIdsBySearchTerm($slug, $term);
			}
		}

		// Get all products that contain search term
		// in its related attributes
		foreach ( $related as $slug ) {
			if ( $term ) {
				$search[] = self::getFilteredIdsBySearchTerm($slug, $term);
			}
		}

		switch ( true ) {

			// If no search queries were performed
			// are pobably nulled or not present, return N ($take)
			// from all the Products
			case (count($search) == 0) :
				return $query;
				break;

			// If one search query was performed, the set
			// is simply its results
			case (count($search) == 1) :
				$intersect = array_values($search)[0];
				break;
			// If multiple search queries were performed, union
			// of their found "ids" is the result
			case (count($search) > 1) :
				$intersect = call_user_func_array('array_merge', $search);
				break;
		}

		// No intersect was found
		// kill further processing by specifying nonsense clause
		if ( !$intersect )
			return $query->where('id', -1);

		// Count occurences of different ids in union
		// and sort array by number of occurences
		$stack = array_count_values($intersect);

		arsort($stack);

		$stack = array_keys($stack);

		// Prepare IDs for use in orderBy
		$ids = implode(',', $stack);

		// Return query with additional WHERE clause containing IN (ids of matching products)
		return $query->whereIn('id', $stack)
			->orderByRaw(DB::raw("FIELD(id, $ids)"));
	}

	public static function getFilteredIdsBySearchTerm($slug, $value)
	{
		// @todo - make smarter to determine whatever attribute is relational or not
		if ( strpos($slug, 'product') !== false ) {
			return DB::table(self::ATTRIBUTE_VALUES_TABLE)
				->join(self::ATTRIBUTES_TABLE, self::ATTRIBUTE_VALUES_TABLE . '.attribute_id', '=', self::ATTRIBUTES_TABLE . '.id')
				->where('slug', $slug)->where('value', 'LIKE', '%' . $value . '%')
				->lists('entity_id');
		} else if ( strpos($slug, 'category') !== false ) {
			$relationships = DB::table(self::ATTRIBUTE_VALUES_TABLE)
				->join(self::ATTRIBUTES_TABLE, self::ATTRIBUTE_VALUES_TABLE . '.attribute_id', '=', self::ATTRIBUTES_TABLE . '.id')
				->where('slug', $slug)->where('value', 'LIKE', '%' .  $value . '%')
				->lists('entity_id');

			// If no such results were found, return empty array
			if ( count($relationships) == 0 ) return [];
			return Product::whereHas('categories', function($q) use ($relationships) {
				$q->whereIn('shop_categories.id', $relationships);
			})->lists('id')->all();
		} else {
			$relationships = DB::table(self::ATTRIBUTE_VALUES_TABLE)
				->join(self::ATTRIBUTES_TABLE, self::ATTRIBUTE_VALUES_TABLE . '.attribute_id', '=', self::ATTRIBUTES_TABLE . '.id')
				->where('slug', $slug)->where('value', 'LIKE', '%' .  $value . '%')
				->lists('entity_id');

			// If no such results were found, return empty array
			if ( count($relationships) == 0 ) return [];

			return Product::whereHas('manufacturer', function($q) use ($relationships) {
				$q->whereIn('shop_manufacturers.id', $relationships);
			})->lists('id')->all();
		}
	}

	public static function getProductAttributes()
	{
		// @todo Cache
		return DB::table(self::ATTRIBUTES_TABLE)
			->where('namespace', 'LIKE', '%product%')
			->lists('slug');
	}

	public static function getProductRelatedAttributes()
	{
		// @todo Cache
		return DB::table(self::ATTRIBUTES_TABLE)
			->where('namespace', 'LIKE', '%manufacturers%')
			->orWhere('namespace', 'LIKE', '%categories%')
			->lists('slug');
	}

	/**
	 * Filter
	 */

	public function scopeFilter($query)
	{
		// Do not filter if no filter is specified
		if ( !Input::has('filter') )
			return $query;

		// Get filters from input
		$filters = Input::get('filter');

		$search = [];

		// Iterate over filters and get search
		// results for all the "search_param" => "search_value" pair
		foreach( $filters as $slug => $value ) {
			if ( $value ) {
				$search[] = self::getFilteredIdsBySlugAndValue($slug, $value);
			}
		}

		switch ( true ) {

			// If no search queries were performed
			// are pobably nulled or not present, return N ($take)
			// from all the Products
			case (count($search) == 0) :
				return $query;
				break;

			// If one search query was performed, the intersect
			// is simply its results
			case (count($search) == 1) :
				$intersect = array_values($search)[0];
				break;

			// If multiple search queries were performed, intersect
			// of their found "ids" is the result
			case (count($search) > 1) :
				$intersect = call_user_func_array('array_intersect', $search);
				break;
		}

		// No intersect was found
		// kill further processing by specifying nonsense clause
		if ( !$intersect )
			return $query->where('id', -1);

		// Return query with additional WHERE clause containing IN (ids of matching products)
		return $query->whereIn('id', $intersect);
	}

	public static function getFilteredIdsBySlugAndValue($slug, $value)
	{
		// @todo - make smarter to determine whatever attribute is relational or not
		if ( strpos($slug, 'product') !== false ) {
			return DB::table(self::ATTRIBUTE_VALUES_TABLE)
				->join(self::ATTRIBUTES_TABLE, self::ATTRIBUTE_VALUES_TABLE . '.attribute_id', '=', self::ATTRIBUTES_TABLE . '.id')
				->where('slug', $slug)->where('value', $value)
				->lists('entity_id');
		} else {
			$relationships = DB::table(self::ATTRIBUTE_VALUES_TABLE)
				->join(self::ATTRIBUTES_TABLE, self::ATTRIBUTE_VALUES_TABLE . '.attribute_id', '=', self::ATTRIBUTES_TABLE . '.id')
				->where('slug', $slug)->where('value', $value)
				->lists('entity_id');

			// If no such results were found, return empty array
			if ( count($relationships) == 0 ) return [];

			return Product::whereHas('manufacturer', function($q) use ($relationships) {
				$q->whereIn('manufacturers.id', $relationships);
			})->lists('id');
		}
	}

	public function coverThumb()
	{
		if ( !$this->product_cover )	// @todo: thumbnail
			return null;

		$medium = app('platform.media')->find($this->product_cover);

		if ( !is_object($medium ) )
			return null;

		return url($medium->thumbnail);
	}

	public function setCategoriesFromArray($categories = [])
	{

		$parent = null;

		foreach ( $categories as $category_name )
		{

			$category_name = trim($category_name);
			$category_slug = str_slug($category_name);

			$category = Category::where('slug', $category_slug)->first();

			if ( !$category ) {
				$category = new Category;

				$normalized_key = 'category_title';

				$category->{$normalized_key} = $category_name;

				if ( $parent ) {
					$category->parent = $parent->id;
				}

				$category->slug = $category_slug;
				$category->save();

			}

			$parent = $category;

			// Attach category
			$this->categories()->attach($category->id);
		}

	}

}