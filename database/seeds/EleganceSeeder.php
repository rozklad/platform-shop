<?php namespace Sanatorium\Shop\Database\Seeds;

class EleganceSeeder extends BaseSeeder {

	/**
	 * Source file for seeding
	 * @var string
	 */
	public $source = '/../source/Elegance.xml';

    public function seedAttributeGroups() {
    }

    /**
     * Process categorytext string and return ids of categories.
     * @param  string $categoryText e.g. "Category 01 | Category 02" meaning that "Category 01" is parent of "Category 02"
     * @return array  Category ids
     */
    public function processCategorytext($categoryText, $separator = '|', $delete = null) {
        $categoryIds = [];
        $categoryTitles = explode($separator, str_replace($delete, '', $categoryText) );

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
        $currency->code = "CZK";

        $currency->save();
    }

    /**
     * Returns manufacturer object based on $manufacturerName attribute from database. New manufacturer object is created if no manufacturer with given name exists.
     * @param  string $manufacturerName Name of the manufacturer
     * @return Manufacturer
     */
    public function findOrAddManufacturer($manufacturerName) {

        if ( $manufacturerName == 'M&S') {
            $slug = 'm-s';
        } else {
            $slug = str_slug($manufacturerName);
        }
        /* Try to find manufacturer with given name */
        $manufacturer = Manufacturer::where('slug', '=', $slug)->first();

        if (!$manufacturer) {
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
        foreach ($orderStateNames as $orderStateName) {
            $orderState = new OrderState;

            $orderState->orderstate_name = (string)$orderStateName;

            $orderState->save();
        }
    }

    public function seedVariants() {
        // Moved to seedProducts()
    }

    public function seedVariantOptions() {
        // Moved to seedProducts()
    }

    public function seedProducts() {
        $productItems = $this->data->SHOPITEM;

        $i = 0;

        foreach ($productItems as $productItem) {

            $i++;

            if ( $i % 2 ) {
                continue;
            }

            /*
             ********************
             * Product
             ********************
             */
            
            $product = new Product;

            $product->product_code = (string)$productItem->CODE;
            $product->product_description = (string)$productItem->DESCRIPTION;
            $product->product_title = (string)$productItem->NAME;
            $product->resluggify();
            $product->save();
            /* Add manufacturer */
            $manufacturerId = $this->findOrAddManufacturer((string)$productItem->MANUFACTURER);
            $product->manufacturer()->attach($manufacturerId);
            /* Add categories */
            $categoryIds = $this->processCategorytext((string)$productItem->CATEGORYTEXT, '|', 'Home |');
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

            /* Add money - plain */
            $plainPrice = (string)$productItem->PRICE;
            if (!empty($plainPrice)) {
                /* Plain */
                $money = new Money;
                $money->currency_id = 1;
                $money->amount = $plainPrice;
                $money->type = "plain";
                $money->save();
                $variantedProduct->money()->attach($money->id);
            }
            /* Add money - vat */
            $vatPrice = (string)$productItem->PRICE_VAT;
            if (!empty($vatPrice)) {
                /* Plain */
                $money = new Money;
                $money->currency_id = 1;
                $money->amount = $vatPrice;
                $money->type = "vat";
                $money->save();
                $variantedProduct->money()->attach($money->id);
            }
            /* Add media */
            $j = 0;
            foreach ($productItem->MEDIA->IMGURL as $imageUrl) {
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


}
