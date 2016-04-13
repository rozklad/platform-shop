<?php namespace Sanatorium\Shop\Database\Seeds;

use DB;
use Cart;
use Illuminate\Database\Seeder;

class BaseSeeder extends Seeder {

	/**
     * Source file for seeding
     * @var string
     */
    public $source;

    public function __construct() 
    {
        /* Assign AttributeRepository to property (used by all the methods to create attributes) */
        $this->attributeRepository = app('Platform\Attributes\Repositories\AttributeRepositoryInterface');

        /* Assign target log file, you will find there all logged information from seeder. */
        $this->logfile = __DIR__ . '/log/seed.log';
    }

    /**
     * Log input to file specified in $this->logfile
     * 
     * @param  string $input Message you want to write to log, date will be automatically prepended to it
     * @return bool          Succesfully written to file (true), could not write to file (false)
     */
    public function log($input = null)
    {
        if (!is_string($input))
            return false;

        return file_put_contents($this->logfile , date('j.n.Y H:i') . ': ' . $input, FILE_APPEND);
    }

    public function run()
    {
        $this->beforeRun();

        $this->clearCart();

        $this->truncate();

        $this->data = $this->getSourceData();

        $this->seedProducts($this->getProducts());

        $this->afterRun();
    }

    public function getProducts()
    {
        return $this->data->SHOPITEM;
    }

    public function seedProducts($products)
    {
        foreach( $products as $productItem ) 
        {
            $product = new Product;

            $product->product_code = (string)$productItem->CODE;
            $product->product_description = (string)$productItem->DESCRIPTION;
            $product->product_title = (string)$productItem->NAME;
            $product->resluggify();
            $product->save();
        }
    }

    public function getSourceData()
    {
        $filepath = __DIR__ . $this->source;
        
        if ( !file_exists($filepath) )
            return [];

        // Get data from file
        $data = file_get_contents( $filepath );

        // Get extension
        $extension = pathinfo( $filepath, PATHINFO_EXTENSION);

        // Decide interpretation method
        switch ($extension) {
        case 'xml':
            // Get xml
            $array = simplexml_load_string($data, null, LIBXML_NOCDATA);
            break;

        default:
            throw new \Exception('File extension is not associated with any interpretation, please save the file with appropriate extension or add support for your filetype');
            break;
        }

        return $array;
    }

    public function clearCart()
    {
        Cart::clear();
    }

    public function truncate()
    {
        // Delete prices connected to products
        DB::table('priced')->where('priceable_type', 'Sanatorium\Shop\Models\Product')->delete();

        // Delete products
        DB::table('shop_products')->truncate();

        // Delete media
        DB::table('media')->truncate();

        // Delete media assign
        DB::table('media_assign')->where('entity_type', 'Sanatorium\Shop\Models\Product')->truncate();

        // Delete shop categories
        DB::table('shop_categories')->truncate();

        // Delete shop money
        DB::table('shop_money')->truncate();

        // Delete product categorization
        DB::table('shop_categorized')->truncate();

        // Delete product manufacturer relation
        DB::table('shop_manufacturized')->truncate();
    }

    public function beforeRun() {}
    public function afterRun () {}

}




