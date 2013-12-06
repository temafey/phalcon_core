<?php
/**
 * @namespace
 */
namespace Engine\Mvc\Module\Service;

use Engine\Mvc\Module\Service\AbstractService;

/**
 * Class View
 *
 * @category   Engine
 * @package    Mvc
 * @subpackage Moduler
 */
class View extends AbstractService
{
    /**
     * Initializes Volt engine
     */
    public function register()
    {
        $di = $this->getDi();
        $eventsManager = $this->getEventsManager();

        $config = $this->_config;
        $moduleDirectory = $this->_module->getModuleDirectory();
        $defaultModuleDir = $this->_module->getDefaultModuleDirectory();

        $di->set('view', function () use ($di, $moduleDirectory, $defaultModuleDir, $eventsManager, $config) {

            $view = new \Phalcon\Mvc\View();
            $view->setViewsDir($moduleDirectory.'/View/');
            $view->setLayoutsDir($defaultModuleDir.'/View/layouts/');

            $view->registerEngines([
                ".volt" => 'viewEngine'
            ]);

            // Attach a listener for type "view"
            if (!$config->application->debug) {
                $eventsManager->attach("view", function ($event, $view) use ($di) {
                    if ($event->getType() == 'notFoundView') {
                        $di->get('logger')->error('View not found - "'.$view->getActiveRenderPath().'"');
                    }
                });

                $view->setEventsManager($eventsManager);
            } elseif ($config->application->profiler) {
                $eventsManager->attach("view", function ($event, $view) use ($di) {
                    if ($di->has('profiler')) {
                        if ($event->getType() == 'beforeRender') {
                            $di->get('profiler')->start();
                        }
                        if ($event->getType() == 'afterRender') {
                            $di->get('profiler')->stop($view->getActiveRenderPath(), 'view');
                        }
                    }
                    if ($event->getType() == 'notFoundView') {
                        $di->get('logger')->error('View not found - "'.$view->getActiveRenderPath().'"');
                    }
                });
                $view->setEventsManager($eventsManager);
            }

            return $view;
        });
    }
} 