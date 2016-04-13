<?php

// autoload_classmap.php @generated by Composer

$vendorDir = dirname(dirname(__FILE__));
$baseDir = dirname($vendorDir);

return array(
    'CreateProductsTable' => $baseDir . '/database/migrations/2015_09_13_111113_create_products_table.php',
    'Sanatorium\\Shop\\Controllers\\Admin\\ProductsController' => $baseDir . '/src/Controllers/Admin/ProductsController.php',
    'Sanatorium\\Shop\\Controllers\\Frontend\\ProductsController' => $baseDir . '/src/Controllers/Frontend/ProductsController.php',
    'Sanatorium\\Shop\\Database\\Seeds\\AvidSeeder' => $baseDir . '/database/seeds/AvidSeeder.php',
    'Sanatorium\\Shop\\Database\\Seeds\\BaseSeeder' => $baseDir . '/database/seeds/BaseSeeder.php',
    'Sanatorium\\Shop\\Database\\Seeds\\EleganceSampleSeeder' => $baseDir . '/database/seeds/EleganceSampleSeeder.php',
    'Sanatorium\\Shop\\Database\\Seeds\\EleganceSeeder' => $baseDir . '/database/seeds/EleganceSeeder.php',
    'Sanatorium\\Shop\\Handlers\\Product\\ProductDataHandler' => $baseDir . '/src/Handlers/Product/ProductDataHandler.php',
    'Sanatorium\\Shop\\Handlers\\Product\\ProductDataHandlerInterface' => $baseDir . '/src/Handlers/Product/ProductDataHandlerInterface.php',
    'Sanatorium\\Shop\\Handlers\\Product\\ProductEventHandler' => $baseDir . '/src/Handlers/Product/ProductEventHandler.php',
    'Sanatorium\\Shop\\Handlers\\Product\\ProductEventHandlerInterface' => $baseDir . '/src/Handlers/Product/ProductEventHandlerInterface.php',
    'Sanatorium\\Shop\\Models\\Product' => $baseDir . '/src/Models/Product.php',
    'Sanatorium\\Shop\\Presenters\\Pagination\\BootstrapThreeCustomPresenter' => $baseDir . '/src/Presenters/Pagination/BootstrapThreeCustomPresenter.php',
    'Sanatorium\\Shop\\Presenters\\Pagination\\BootstrapThreeLitePresenter' => $baseDir . '/src/Presenters/Pagination/BootstrapThreeLitePresenter.php',
    'Sanatorium\\Shop\\Providers\\ProductServiceProvider' => $baseDir . '/src/Providers/ProductServiceProvider.php',
    'Sanatorium\\Shop\\Repositories\\Product\\ProductRepository' => $baseDir . '/src/Repositories/Product/ProductRepository.php',
    'Sanatorium\\Shop\\Repositories\\Product\\ProductRepositoryInterface' => $baseDir . '/src/Repositories/Product/ProductRepositoryInterface.php',
    'Sanatorium\\Shop\\Validator\\Product\\ProductValidator' => $baseDir . '/src/Validator/Product/ProductValidator.php',
    'Sanatorium\\Shop\\Validator\\Product\\ProductValidatorInterface' => $baseDir . '/src/Validator/Product/ProductValidatorInterface.php',
    'Sanatorium\\Shop\\Widgets\\Product' => $baseDir . '/src/Widgets/Product.php',
);
