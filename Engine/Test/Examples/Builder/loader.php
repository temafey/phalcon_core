<?php

$loader = new \Phalcon\Loader();

//Register some namespaces
$loader->registerNamespaces(
    array(
        'Engine' => '../../../',
    )
);

// register autoloader
$loader->register();