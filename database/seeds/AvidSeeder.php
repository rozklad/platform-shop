<?php namespace Sanatorium\Shop\Database\Seeds;

use Config;
use DB;
use Fishcat\Shop\Models\AttributeGroup;
use Fishcat\Shop\Models\Category;
use Fishcat\Shop\Models\Condition;
use Fishcat\Shop\Models\ConditionEloq;
use Fishcat\Shop\Models\Currency;
use Fishcat\Shop\Models\Manufacturer;
use Fishcat\Shop\Models\Money;
use Fishcat\Shop\Models\MoneyType;
use Fishcat\Shop\Models\OrderState;
use Fishcat\Shop\Models\Product;
use Fishcat\Shop\Models\ProductSpecifiedVariant;
use Fishcat\Shop\Models\Variant;
use Fishcat\Shop\Models\VariantOption;
use Fishcat\Shop\Models\VariantedProduct;
use Fishcat\Shop\Models\VariantedProductSpecifiedVariant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Platform\Attributes\Models\Attribute;
use Platform\Attributes\Models\Value;
use Platform\Tags\Models\Tag;
use Sentinel;

class AvidSeeder extends BaseSeeder {

    protected $source = '/../source/Avid.xml';

    public function seedAttributeGroups() {
    }

    /**
     * Process categorytext string and return ids of categories.
     * @param  string $categoryText e.g. "Category 01 | Category 02" meaning that "Category 01" is parent of "Category 02"
     * @return array  Category ids
     */
    public function processCategorytext($categoryText, $separator = '|') {
        $categoryIds = [];
        $categoryTitles = explode($separator, $categoryText);

        $parent = 0;
        $categoryTitleAttribute = Attribute::where('namespace', '=', 'fishcat/shop.categories')->where('slug', '=', 'category_title')->first();
        $categoryTitleAttributeId = $categoryTitleAttribute->id; 
        foreach ($categoryTitles as $categoryTitle) {
            $categoryTitle = trim($categoryTitle);
            $attributeValue = Value::where('attribute_id', '=', $categoryTitleAttributeId)->where('value', '=', $categoryTitle)->first();
            if ($attributeValue == null) {
                /* If no category with given title found create new */
                $category = new Category;

                $category->category_title = $categoryTitle;
                $category->parent = $parent;
                $category->resluggify();

                $category->save();
                $parent = $category->id;
                $categoryIds[] = $category->id;
            } else {
                $parent = $attributeValue->entity_id;
                $categoryIds[] = $attributeValue->entity_id;
            }
        }

        return $categoryIds;
    }

    public function seedCategories() {
        // Moved to seedProducts()
    }

    public function seedCurrencies() {
        /* CZK */
        $currency = new Currency;

        $currency->id = 1;
        $currency->rate = 1;
        $currency->amount = ',-';
        $currency->cent_factor = 100;
        $currency->code = "cs";

        $currency->save();

        /* € */
        $currency = new Currency;

        $currency->id = 2;
        $currency->rate = 27.01;
        $currency->amount = " €";
        $currency->cent_factor = 100;
        $currency->code = "sk";

        $currency->save();
    }

    /**
     * Returns manufacturer object based on $manufacturerName attribute from database. New manufacturer object is created if no manufacturer with given name exists.
     * @param  string $manufacturerName Name of the manufacturer
     * @return Manufacturer
     */
    public function findOrAddManufacturer($manufacturerName) {
        $slug = Str::slug($manufacturerName);
        /* Try to find manufacturer with given name */
        $manufacturer = Manufacturer::where('slug', '=', $slug)->first();
        if ($manufacturer == null) {
            /* If no manufacturer with given name found create new */
            $manufacturer = new Manufacturer;

            $manufacturer->manufacturer_name = (string)$manufacturerName;
            $manufacturer->resluggify();

            $manufacturer->save();
        }
        return $manufacturer->id;
    }

    public function seedManufacturers() {
        // Moved to seedProducts()
    }

    public function seedOrderStates() {
        $orderStateNames = ['Vytvořeno', 'Uhrazeno', 'Dokončeno'];
        $i = 1;
        foreach ($orderStateNames as $orderStateName) {
            $orderState = OrderState::find($i);
            if (!$orderState) {
                $orderState = new OrderState;
                $orderState->id = $i;
            }
            $orderState->orderstate_name = (string)$orderStateName;

            $orderState->save();
            $i++;
        }
    }

    public function seedVariants() {
        // Moved to seedProducts()
    }

    public function seedVariantOptions() {
        // Moved to seedProducts()
    }

    public function seedProducts() {
        $czkCurrency = Currency::where('code', '=', 'cs')->first();
        $eurCurrency = Currency::where('code', '=', 'sk')->first();

        $productCodeAttributeId = Attribute::where('slug', '=', 'product_code')->first();

        $productItems = $this->data->SHOPITEM;
        foreach ($productItems as $productItem) {
            /*
             ********************
             * Product
             ********************
             */
            
            $productCode = (string)$productItem->PRODUCTNO;
            $value = Value::where('attribute_id', '=', $productCodeAttributeId)->where('value', '=', $productCode)->first();
            $product = null;
            if (!$value) {
                $product = new Product;
                $product->product_code = (string)$productItem->PRODUCTNO;
            } else {
                $product = Product::where('id', '=', $value->entity_id);
            }
            $product->product_description = (string)$productItem->DESCRIPTION;
            $product->product_title = (string)$productItem->PRODUCT;
            $product->resluggify();
            $product->save();
            /* Add manufacturer */
            $manufacturerId = $this->findOrAddManufacturer((string)$productItem->MANUFACTURER);
            $product->manufacturer()->attach($manufacturerId);
            /* Add categories */
            $categoryIds = $this->processCategorytext((string)$productItem->CATEGORYTEXT);
            foreach ($categoryIds as $categoryId) {
                $product->categories()->attach($categoryId);
            }
            $product->save();

            /*
             ********************
             * VariantedProduct
             ********************
             */
            
            $variantedProduct = new VariantedProduct;
            $variantedProduct->product_id = $product->id;
            $variantedProduct->variantedproduct_ean = $product->product_code;
            $variantedProduct->variantedproduct_code = $product->product_code;
            $variantedProduct->save();
            $variantedProduct = VariantedProduct::find($variantedProduct->id);

            /* Money */

            /* Add money - plain */
            $plainPrice = (string)$productItem->PRICE;
            if (!empty($plainPrice)) {
                /* CZK */
                $money = new Money;
                $money->currency_id = $czkCurrency->id;
                $money->amount = $plainPrice;
                $money->type = "plain";
                $money->save();
                $variantedProduct->money()->attach($money->id);
                /* EUR */
                $money = new Money;
                $money->currency_id = $eurCurrency->id;
                $money->amount = $plainPrice / $eurCurrency->rate;
                $money->type = "plain";
                $money->save();
                $variantedProduct->money()->attach($money->id);
            }
            /* Add money - vat */
            $vatPrice = (string)$productItem->PRICE_VAT;
            if (!empty($vatPrice)) {
                /* CZK */
                $money = new Money;
                $money->currency_id = $czkCurrency->id;
                $money->amount = $vatPrice;
                $money->type = "vat";
                $money->save();
                $variantedProduct->money()->attach($money->id);
                /* EUR */
                $money = new Money;
                $money->currency_id = $eurCurrency->id;
                $money->amount = $vatPrice / $eurCurrency->rate;
                $money->type = "vat";
                $money->save();
                $variantedProduct->money()->attach($money->id);
            }
            /* Add media */
            $j = 0;
            foreach ($productItem->IMGURL as $imageUrl) {
                $imageUrl = (string)$imageUrl;
                if (!empty($imageUrl)) {
                    $imageUrl = $this->createImageUrl($imageUrl);
                    $variantedProduct->addMedia($imageUrl, "variantedproduct_image", false, $j === 0);
                    $j++;
                }
            }
            $variantedProduct->save();
        }
    }

    public function seedVariantedProducts() {
    }

    public function seedTagsRandomly() {
        /* Create tag */
        $tag = new Tag;
        $tag->name = "Featured";
        $tag->namespace = "fishcat/shop.products";
        $tag->resluggify();
        $tag->save();

        /* Tag randomly products */
        $amount = 10 + rand(15);
        for ($i = 0; $i < $amount; $i++) {
            $product = Product::random();
            
        }
    }

    public function seed() {
        $this->seedAttributeGroups();
        $this->seedCategories();
        $this->seedCurrencies();
        $this->seedManufacturers();
        $this->seedOrderStates();
        $this->seedVariants();
        $this->seedVariantOptions();
        $this->seedDefaultDeliveryAndPaymentTypes();
        $this->seedProducts();
        $this->seedVariantedProducts();
    }

    /**
     * Run the database seeds, this method uses defined input ($this->source) to seed database with appropriate objects and their respective relations and attributes.
     *
     * @return void
     */
    public function run()
    {
        // Prevent server aborting after
        // expired time limit (if possible)
        set_time_limit(0);

        // Perform default actions before any other seed actions
        // Save current user as author
        $this->user = Sentinel::getUser();;

        // Get seed data
        $this->data = $this->getData();

        $this->clearCart();
        
        $this->truncate();

        $this->seed();

        //DB::unprepared("CALL create_products_mv_table();");
    }
}
