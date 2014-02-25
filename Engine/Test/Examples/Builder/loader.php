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
        'Engine' => '../../../',
    )
);

// register autoloader
$loader->register();