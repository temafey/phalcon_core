<?php
/**
 * Created by Slava Basko.
 * Email: basko.slava@gmail.com
 * Date: 2/25/14
 * Time: 1:26 PM
 */

require 'loader.php';

use Engine\Builder\Form as FormBuilder;
use Engine\Builder\Script\Color;

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