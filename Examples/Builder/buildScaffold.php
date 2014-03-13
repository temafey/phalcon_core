<?php

require 'loader.php';

use Engine\Builder\Model as ModelBuilder;
use Engine\Builder\Form as FormBuilder;
use Engine\Builder\Grid as GridBuilder;
use Engine\Builder\Script\Color;

print Color::head('Start creating models') . PHP_EOL;
$ModelBuilder = new ModelBuilder([
    'table_name' => 'front_category'
]);
$ModelBuilder->build();

$ModelBuilder = new ModelBuilder([
    'table_name' => 'front_product_type'
]);
$ModelBuilder->build();

$ModelBuilder = new ModelBuilder([
    'table_name' => 'front_product'
]);
$ModelBuilder->build();


print Color::head('Start creating forms') . PHP_EOL;
$ModelBuilder = new FormBuilder([
    'table_name' => 'front_category'
]);
$ModelBuilder->build();

$ModelBuilder = new FormBuilder([
    'table_name' => 'front_product_type'
]);
$ModelBuilder->build();

$ModelBuilder = new FormBuilder([
    'table_name' => 'front_product'
]);
$ModelBuilder->build();


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