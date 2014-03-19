<?php
/**
 * @namespace
 */
namespace Engine\Mvc\Module\Service;

use Engine\Mvc\Module\Service\AbstractService,
    Engine\Acl\Authorizer;

/**
 * Class Acl
 *
 * @category   Engine
 * @package    Mvc
 * @subpackage Moduler
 */
class Auth extends AbstractService
{
    /**
     * Initializes acl
     */
    public function register()
    {
        $di = $this->getDi();
        $options = $this->_config->application->acl->toArray();

        $di->set('auth', function () use ($options, $di) {
            $adapter = new Authorizer($options, $di);
            return $adapter;
        });
    }
} 