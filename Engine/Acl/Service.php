<?php
/**
 * @namespace
 */
namespace Engine\Acl;

use Phalcon\Acl as PhAcl,
    Phalcon\Acl\Resource as AclResource,
    Phalcon\Acl\Adapter\Memory as AclMemory;

/**
 * Class Service
 *
 * @category   Engine
 * @package    Acl
 */
class Service implements \Phalcon\DI\InjectionAwareInterface
{
    use \Engine\Tools\Traits\DIaware;

    const ACL_CACHE_KEY = "acl_data.cache";

    const ROLE_TYPE_ADMIN = 'admin';
    const ACL_ADMIN_AREA = 'admin_area';

    /**
     * @var \Phalcon\Acl\Adapter\Memory
     */
    protected $_acl;

    /**
     * @param \Phalcon\DiInterface $di
     */
    public function __construct(\Phalcon\DiInterface $di = null)
    {
        $this->setDi($di);
    }

    /**
     * Get acl system
     *
     * @return \Phalcon\Acl\Adapter\Memory
     */
    public function getAdapter()
    {
        if (!$this->_acl) {
            $cacheData = false;
            $acl = null;
            if ($this->_di->has('cacheData')) {
                $cacheData = $this->_di->get('cacheData');
                $acl = $cacheData->get(self::ACL_CACHE_KEY);
            }
            if ($acl === null) {
                $acl = new AclMemory();
                $acl->setDefaultAction(PhAcl::DENY);

                $aclAdapter = $this->_di->get('aclAdapter');
                $aclAdapter->setDefaultAction(PhAcl::DENY);

                if (!$aclAdapter instanceof \Phalcon\Acl\Adapter) {
                    throw new \Engine\Exception('Acl adapter not instance of Phalcon\Acl\Adapter');
                }
                // prepare Roles
                $roles = $aclAdapter->getRoles();
                foreach ($roles as $role) {
                    $acl->addRole($role);
                }
                $acl->addRole(self::ROLE_TYPE_ADMIN);

                // Defining admin area
                $adminArea = new AclResource(self::ACL_ADMIN_AREA);
                // Add "admin area" resource
                $acl->addResource($adminArea, '*');
                $acl->allow(self::ROLE_TYPE_ADMIN, '*', '*');
                $acl->allow(self::ROLE_TYPE_ADMIN, self::ACL_ADMIN_AREA, '*');

                // Getting objects that is in acl
                // Looking for all models in modelsDir and check @Acl annotation
                $objects = [
                    self::ACL_ADMIN_AREA => [
                        'actions' => ['*']
                    ]
                ];
                $config = $this->_di->get('config');

                foreach ($this->_di->get('modules') as $module => $enabled) {
                    if (!$enabled) {
                        continue;
                    }

                    $moduleName = ucfirst($module);
                    $controllerPath = $config->application->modulesDir.$moduleName.'/Controller';
                    if (file_exists($controllerPath)) {
                        $files = scandir($controllerPath); // get all file names

                        foreach ($files as $file) { // iterate files
                            if ($file == "." || $file == "..") {
                                continue;
                            }
                            $controllerClass = ucfirst(str_replace('.php', '', $file));
                            $controllerClassName = str_replace('Controller', '', $controllerClass);
                            $class = sprintf('\%s\Controller\%s', $moduleName, $controllerClass);
                            $object = $this->getObjectAcl($class);
                            if ($object == null) {
                                continue;
                            }
                            $resource = $this->getResource($moduleName, $controllerClassName);
                            $objects[$resource]['actions'] = $object->actions;
                            $objects[$resource]['options'] = $object->options;
                        }

                        // add objects to resources
                        foreach ($objects as $resource => $object) {
                            if (empty($object['actions'])) {
                                $object['actions'] = [];
                            }
                            $aclAdapter->addResource($resource, $object['actions']);
                            $acl->addResource($resource, $object['actions']);
                        }
                    }
                }

                foreach ($roles as $role) {
                    $roleName = $role->getName();
                    foreach ($objects as $resource => $object) {
                        foreach ($object['actions'] as $actions) {
                            if ($aclAdapter->isAllowed($roleName, $resource, $actions)) {
                                $acl->allow($roleName, $resource, $actions);
                            } else {
                                $acl->deny($roleName, $resource, $actions);
                            }
                        }
                    }
                }

                if ($cacheData) {
                    $cacheData->save(self::ACL_CACHE_KEY, $acl, 3600);
                }
            }

            $this->_acl = $acl;
        }

        return $this->_acl;
    }

    /**
     * Parse object annotations for find acl rules
     *
     * @param $objectName
     * @return null|\stdClass
     */
    public function getObjectAcl($objectName)
    {
        $object = new \stdClass();
        $object->name = $objectName;
        $object->actions = [];
        $object->options = [];

        $reader = new \Phalcon\Annotations\Adapter\Memory();
        $reflector = $reader->get($objectName);
        $annotations = $reflector->getClassAnnotations();
        if ($annotations && $annotations->has('Acl')) {
            $annotation = $annotations->get('Acl');
            if ($annotation->hasNamedArgument('actions')) {
                $object->actions = $annotation->getArgument('actions');
            }
            if ($annotation->hasNamedArgument('options')) {
                $object->options = $annotation->getArgument('options');
            }
        } else {
            return null;
        }

        return $object;
    }

    /**
     * Return resource name
     *
     * @param string $moduleName
     * @param string $controllerName
     * @return string
     */
    public function getResource($moduleName, $controllerName)
    {
        return strtolower(trim($moduleName, " /\\")."_".trim($controllerName, " /\\"));
    }

    /**
     * Check access by role and mvc module, controller and action names
     *
     * @param string $role
     * @param string $moduleName
     * @param string $controllerName
     * @param string $actionName
     * @param bool $checkResource
     * @return bool
     */
    public function isAllowed($role, $moduleName, $controllerName, $actionName, $checkResource = false)
    {
        $resource = $this->getResource($moduleName, $controllerName);
        $access = $actionName;
        $adapter = $this->getAdapter();

        if ($checkResource && !$adapter->isResource($resource)) {
            return true;
        }

        return ($adapter->isAllowed($role, $resource, $access) == \Phalcon\Acl::ALLOW) ? true : false;
    }

    /**
     * Clear acl cache. The acl will be rewrited.
     */
    public function clearAcl()
    {
        $this->_di->get('cacheData')->delete(self::ACL_CACHE_KEY);
    }

}