<?php
/**
 * @namespace
 */
namespace Engine\Mvc\Module\Service;

use Engine\Mvc\Module\Service\AbstractService,
    Phalcon\Mvc\Dispatcher as MvcDispatcher,
    Phalcon\Events\Manager as EventsManager,
    Phalcon\Mvc\Dispatcher\Exception as DispatchException;

/**
 * Class Acl
 *
 * @category   Engine
 * @package    Mvc
 * @subpackage Moduler
 */
class Acl extends AbstractService
{
    /**
     * Initializes acl
     */
    public function register()
    {
        $di = $this->getDi();
        $eventsManager = $this->getEventsManager();

        $aclAdapter = $this->_getAclAdapter($this->_config->application->acl->adapter);
        if (!$aclAdapter) {
            throw new \Engine\Exception("Acl adapter '{$this->_config->application->acl->adapter}' not exists!");
        }
        $options = $this->_config->application->acl->toArray();
        $adapter = new $aclAdapter($options, $di);
        $di->set('aclAdapter', $adapter);

        $aclDispatcher = new \Engine\Acl\Dispatcher($di);
        $eventsManager->attach('dispatch:beforeDispatch', $aclDispatcher);
    }

    /**
     * Return acl adapter full class name
     *
     * @param string $name
     * @return string
     */
    protected function _getAclAdapter($name)
    {
        $adapter = '\Engine\Acl\Adapter\\'.ucfirst($name);
        if (!class_exists($adapter)) {
            return false;
        }

        return $adapter;
    }
} 