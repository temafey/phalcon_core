<?php

require 'loader.php';

use Engine\Builder\GridExtJs as GridBuilder;
use Engine\Builder\Script\Color;

print Color::head('Start creating extjs grids') . PHP_EOL;


$ModelBuilder = new GridBuilder([
    'table_name' => 'front_category'
]);
$ModelBuilder->build();


$ModelBuilder = new GridBuilder([
    'table_name' => 'front_product_type'
]);
$ModelBuilder->build();


$ModelBuilder = new GridBuilder([
    'table_name' => 'front_product'
]);
$ModelBuilder->build();
