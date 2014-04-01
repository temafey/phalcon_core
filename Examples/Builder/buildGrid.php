<?php

require 'loader.php';

use Engine\Builder\Grid as GridBuilder;
use Engine\Builder\Script\Color;

print Color::head('Start creating grids') . PHP_EOL;


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
