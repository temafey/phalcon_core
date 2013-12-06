<?php
/**
 * @namespace
 */
namespace Engine\Acl;

/**
 * Class Dispatcher
 *
 * @category   Engine
 * @package    Acl
 */
class Dispatcher
{
    use \Engine\Tools\Traits\DIaware;

    /**
     * @param \Phalcon\DiInterface $di
     */
    public function __construct(\Phalcon\DiInterface $di = null)
    {
        $this->setDi($di);
    }

    /**
     * This action is executed before execute any action in the application
     */
    public function beforeDispatch(\Phalcon\Events\Event $event, \Phalcon\Mvc\Dispatcher $dispatcher)
    {
        // check installation
        /*if (!$this->_di->get('config')->installed) {
            $this->_di->set('installationRequired', true);
            if ($dispatcher->getControllerName() != 'install') {
                return $dispatcher->forward([
                    'module' => 'core',
                    "controller" => "install",
                    "action" => "index"
                ]);
            }
            return;
        }*/

        $viewer = $this->_di->get('viewer');
        $aclService = new Service($this->_di);
        $acl = $aclService->getAdapter();

        $module = $dispatcher->getModuleName();
        $controller = $dispatcher->getControllerName();
        $action = $dispatcher->getActionName();

        $resource = $aclService->getResource($module, $controller);
        $access = $action;

        // check admin area
        if (substr($controller, 0, 5) == 'admin') {
            if ($acl->isAllowed($viewer->getRole(), Service::ACL_ADMIN_AREA, '*') != \Phalcon\Acl::ALLOW) {
                return $dispatcher->forward([
                    'module' => \Engine\Application::$defaultModule,
                    'namespace' => ucfirst(\Engine\Application::$defaultModule).'\Controller',
                    "controller" => 'error',
                    "action" => 'show404'
                ]);
            }
        } else {
            if ($acl->isResource($resource) && $acl->isAllowed($viewer->getRole(), $resource, $access) != \Phalcon\Acl::ALLOW) {
                return $dispatcher->forward([
                    'module' => \Engine\Application::$defaultModule,
                    'namespace' => ucfirst(\Engine\Application::$defaultModule).'\Controller',
                    "controller" => 'error',
                    "action" => 'show404'
                ]);
            }
        }
    }
}