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

        $di->set('acl', function () use ($di) {
            $acl = new \Engine\Acl\Service($di);
            return $acl;
        });

        $options = $this->_config->application->acl->toArray();
        $aclAdapter = $this->_getAclAdapter($options['adapter']);
        $di->set('aclAdapter', function () use ($aclAdapter, $options, $di) {
            if (!$aclAdapter) {
                throw new \Engine\Exception("Acl adapter '{$options['adapter']}' not exists!");
            }
            $adapter = new $aclAdapter($options, $di);
            return $adapter;
        });

        $aclDispatcher = new \Engine\Acl\Dispatcher($di);
        $eventsManager->attach('dispatch:beforeDispatch', $aclDispatcher);

        if (isset($options['adminModule'])) {
            $registry = $di->get('registry');
            $registry->adminModule = $options['adminModule'];

            $registry2 = $di->get('registry');
        }
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