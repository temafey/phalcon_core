<?php
/**
 * @namespace
 */
namespace Engine\Application\Service;

use Engine\Application\Service\AbstractService;

/**
 * Class Router
 *
 * @category   Engine
 * @package    Application
 * @subpackage Service
 */
class Router extends AbstractService
{
    /**
     * Initializes router system
     */
    public function init()
    {
        $di = $this->getDi();
        $eventsManager = $this->getEventsManager();

        $routerCacheKey = 'router_data.cache';
        $cacheData = $di->get('cacheData');
        $router = $cacheData->get($routerCacheKey);

        if ($this->_config->application->debug || $router === null) {

            $saveToCache = ($router === null);

            // load all controllers of all modules for routing system
            $modules = $di->get('modules');

            //Use the annotations router
            $defaultModule = $this->_config->application->defaultModule;
            $router = new \Phalcon\Mvc\Router\Annotations(false);
            $router->removeExtraSlashes(true);
            $router->setDefaultModule($defaultModule);
            $router->setDefaultNamespace(ucfirst($defaultModule) . '\Controller');
            $router->setDefaultController("index");
            $router->setDefaultAction("index");

            $router->add('/:module/:controller/:action', [
                'module' => 1,
                'controller' => 2,
                'action' => 3
            ]);

           /*$router->notFound([
                'module' => $defaultModule,
                'namespace' => ucfirst($defaultModule) . '\Controller',
                'controller' => 'error',
                'action' => 'show404'
            ]);
*/
            //Read the annotations from controllers
            foreach ($modules as $module => $enabled) {
                if (!$enabled) {
                    continue;
                }

                $files = scandir($this->_config->application->modulesDir . ucfirst($module) . '/Controller'); // get all file names
                foreach ($files as $file) { // iterate files
                    if ($file == "." || $file == "..") {
                        continue;
                    }
                    $controller = ucfirst($module).'\Controller\\'.str_replace('Controller.php', '', $file);
                    if (strpos($file, 'Controller.php') !== false) {
                        $router->addModuleResource(strtolower($module), $controller);
                    }
                }
            }
            if ($saveToCache) {
                $cacheData->save($routerCacheKey, $router, 3600); // 30 days cache
            }
        }

        $di->set('router', $router);
    }
} 