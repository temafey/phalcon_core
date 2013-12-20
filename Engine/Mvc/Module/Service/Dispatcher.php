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
 * Class Dispatcher
 *
 * @category   Engine
 * @package    Mvc
 * @subpackage Moduler
 */
class Dispatcher extends AbstractService
{
    /**
     * Initializes dispatcher
     */
    public function register()
    {
        $di = $this->getDi();
        $eventsManager = $this->getEventsManager();

        $config = $this->_config;
        $defaultModuleDir = $this->_module->getDefaultModuleDirectory();
        $di->set('defualtModuleDir', function() use ($defaultModuleDir) {
            return $defaultModuleDir;
        });

        $moduleDirectory = $this->_module->getModuleDirectory();
        $di->set('moduleDirectory', function() use ($moduleDirectory) {
            return $moduleDirectory;
        });

        $di->set('dispatcher', function() use ($di, $eventsManager, $config)  {
            // Create dispatcher
            $dispatcher = new MvcDispatcher();

            //Attach a listener
            $eventsManager->attach("dispatch:beforeException", function($event, \Phalcon\Mvc\Dispatcher $dispatcher, $exception) use ($di, $config)   {

                if ($config->application->debug && $di->has('logger')) {
                    $logger = $di->get('logger');
                    $logger->error($exception->getMessage());
                }

                //Handle 404 exceptions
                if ($exception instanceof DispatchException) {
                    $dispatcher->forward([
                        'module' => \Engine\Application::$defaultModule,
                        'namespace' => ucfirst(\Engine\Application::$defaultModule).'\Controller',
                        'controller' => 'error',
                        'action' => 'show404'
                    ]);
                    return false;
                }

                //Handle other exceptions
                $dispatcher->forward([
                    'module' => \Engine\Application::$defaultModule,
                    'namespace' => ucfirst(\Engine\Application::$defaultModule).'\Controller',
                    'controller' => 'error',
                    'action' => 'show503'
                ]);

                return false;
            });

            $eventsManager->attach("dispatch:beforeDispatchLoop", function($event, \Phalcon\Mvc\Dispatcher $dispatcher) {
                $dispatcher->setControllerName(\Phalcon\Text::lower($dispatcher->getControllerName()));
            });

            $dispatcher->setEventsManager($eventsManager);

            return $dispatcher;
        });
    }
} 