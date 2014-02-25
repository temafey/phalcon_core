<?php
/**
 * Created by Slava Basko.
 * Email: basko.slava@gmail.com
 * Date: 2/25/14
 * Time: 1:57 PM
 */

$loader = new \Phalcon\Loader();

//Register some namespaces
$loader->registerNamespaces(
    array(
        'Example\Base'    => 'vendor/example/base/',
        'Example\Adapter' => 'vendor/example/adapter/',
        'Example'         => 'vendor/example/',
    )
);

// register autoloader
$loader->register();