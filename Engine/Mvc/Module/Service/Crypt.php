<?php
/**
 * @namespace
 */
namespace Engine\Mvc\Module\Service;

use Engine\Mvc\Module\Service\AbstractService;

/**
 * Class Crypt
 *
 * @category   Engine
 * @package    Mvc
 * @subpackage Moduler
 */
class Crypt extends AbstractService
{
    /**
     * Initializes viewer
     */
    public function register()
    {
        $di = $this->getDi();
        $key = $this->_config->application->crypt->key;

        $di->set('crypt', function () use ($key) {
            $crypt = new \Phalcon\Crypt();
            $crypt->setKey($key);
            return $crypt;
        });
    }
} 