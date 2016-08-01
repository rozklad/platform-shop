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

// Thumbs dependencies
use StorageUrl;
use Platform\Media\Models\Media;
use Platform\Media\Styles\Style;
use Storage;
use Illuminate\Support\Str;
use Sanatorium\Inputs\Traits\ThumbableTrait;

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
		StockTrait,
        ThumbableTrait;

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

	public function variants()
	{
		return $this->hasMany('Sanatorium\Variants\Models\Variant', 'parent_id');
	}

	public function getUrlAttribute()
	{
		if ( class_exists('\Category') ) {
			// Categories are installed, return better optimized SEO url

			$base = '';

			foreach ($this->categories as $category) {
				$base .= $category->slug . '/';
			}

			return url($base . $this->slug);
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
                    ->groupBy('shop_products.id')
					->select('shop_products.*')
					->orderBy( \DB::raw('CAST('.config('database.connections.mysql.prefix').'shop_money.amount AS DECIMAL)'), $orderway);
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
				$q->whereIn('categories.id', $relationships);
			})->lists('id')->all();
		} else {
			$relationships = DB::table(self::ATTRIBUTE_VALUES_TABLE)
				->join(self::ATTRIBUTES_TABLE, self::ATTRIBUTE_VALUES_TABLE . '.attribute_id', '=', self::ATTRIBUTES_TABLE . '.id')
				->where('slug', $slug)->where('value', 'LIKE', '%' .  $value . '%')
				->lists('entity_id');

			// If no such results were found, return empty array
			if ( count($relationships) == 0 ) return [];

			return Product::whereHas('manufacturers', function($q) use ($relationships) {
				$q->whereIn('manufacturers.id', $relationships);
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

	/**
	 * Thumbs work
	 * @var string
	 */

	protected $cover_attribute = 'product_cover';

	protected $gallery_attribute = 'product_gallery';

	public $cover_object;

	public $cover_image;

	public function getGalleryImages($size = 'full', $thumbsize = 150)
	{
		$images = $this->{$this->gallery_attribute};

		$output = [];

        if ( is_array($images) )
        {

            foreach ( $images as $media_id )
            {

                $media = app('platform.media')->find($media_id);

                $thumb = StorageUrl::url(self::getSizeUrl($media, $thumbsize, $thumbsize));

                $full = StorageUrl::url(self::getSizeUrl($media, $size, $size));

                $output[ $thumb ] = $full;

            }

        }

		return $output;
	}

	public function getGalleryImageObjects()
	{
		$ids = $this->getGalleryImageIds();

		$output = [];

		if ( is_array($ids) )
		{

			foreach ( $ids as $media_id )
			{

				$output[ $media_id ] = app('platform.media')->find($media_id);
			}

		}

		return $output;
	}

	public function getGalleryImageIds()
	{
		return $this->{$this->gallery_attribute};
	}

	public function coverThumb($size = 'full')
	{
		if ( !$this->{$this->cover_attribute} )	// @todo: thumbnail
			return null;

		$medium = $this->getCoverObject();

		if ( !is_object($medium) )
			return null;

		$this->cover_object = $medium;
		$this->cover_image = StorageUrl::url( self::getSizeUrl($medium, $size) );

		return $this->cover_image;

	}

	public function getCoverObject()
	{
		return $this->getMediaObject($this->{$this->cover_attribute});
	}

	public function getMediaObject($media_id = 0)
	{
		return app('platform.media')->find($media_id);
	}

	/**
	 * @todo move to thumbs package
	 * @param $path
	 * @param $size
	 */
	public static function getSizeUrl($media, $size)
	{
		switch ( $size )
		{
			// Sanatorium/Thumbs thumb macro
			default:
				return self::thumbnailPath($media, $size, $size);
				break;
		}

	}

	public function regenerateThumbnail(Media $media)
	{
		if ( !$this->styles )
			$this->styles = app('platform.media.manager');

		if ( !$this->intervention )
			$this->intervention = app('image');

		$original = StorageUrl::url( $media->path );

		// Loop through all the registered styles
		foreach ($this->styles->getStyles() as $name => $style) {
			// Initialize the style
			call_user_func($style, $style = new Style($name));

			// Check if the uploaded file mime type is valid
			if ($style->mimes && ! in_array($media->mime, $style->mimes)) {
				continue;
			}

			$path = $media->path;

			if ( empty($original) ) return false;

			$contents = @file_get_contents($original);

			if ( empty($contents) ) return false;

			// Create the thumbnail
			$image = $this->intervention->make($contents);

			if ( $style->width )
			{
				$image->resize(null, $style->width, function ($constraint)
				{
					$constraint->aspectRatio();
					$constraint->upsize();
				});
			}

			$image->encode( \Sanatorium\Thumbs\Styles\Macros\ThumbsMacro::getExtension($media) );

			Storage::disk('s3')->put(
				str_replace(public_path(), null, $this->getUploadPath($media, $style)),
				$image->getEncoded()
			);

		}

	}

	public function getUploadPath($media, $style)
	{
		$width = $style->width;
		$height = $style->height;

		$name = Str::slug(implode('-', [ $width, $height ?: $width ]));

		$extension = \Sanatorium\Thumbs\Styles\Macros\ThumbsMacro::getExtension($media);

		return "{$style->path}/{$media->id}_{$name}.{$extension}";
	}

	public function regenerateThumbnails()
	{
		$images = [
			$this->getCoverObject()
		];

		$images = array_merge($images, $this->getGalleryImageObjects());

		foreach( $images as $media )
		{
			if ( !is_null($media) )
				$this->regenerateThumbnail($media);
		}

	}

	public function hasCoverImage()
	{
		if ( !$this->product_cover )
			return false;

		$medium = app('platform.media')->find($this->product_cover);

		if ( !is_object($medium) )
			return false;

		return true;
	}

    public function setCategoriesFromArray($categories = [])
    {

        $parent = null;

        $categoriesrepo = app('sanatorium.categories.category');

        foreach ( $categories as $category_name )
        {

            $category_name = trim($category_name);
            $category_slug = str_slug($category_name);

            $category = $categoriesrepo->where('slug', $category_slug);

            if ( is_object($parent) )
                $category->where('parent', $parent->id);

            $category = $category->first();

            if ( !$category ) {

                if ( is_object($parent) ) {
                    $parent_id = $parent->id;
                } else {
                    $parent_id = 0;
                }

                list($messages, $category) = $categoriesrepo->create([
                    'category_title' => $category_name,
                    'parent' => $parent_id,
                    'slug' => $category_slug
                ]);

            }

            $parent = $category;

            // Attach category
            $this->categories()->attach($category->id);
        }

    }

}