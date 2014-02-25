<?php
/**
 * Created by Slava Basko.
 * Email: basko.slava@gmail.com
 * Date: 2/25/14
 * Time: 1:26 PM
 */

use Engine\Builder\Model as ModelBuilder;
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