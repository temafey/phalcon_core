<?php
/**
 * @namespace
 */
namespace Engine\Application\Service;

use Engine\Application\Service\AbstractService;

/**
 * Class Loader
 *
 * @category   Engine
 * @package    Application
 * @subpackage Service
 */
class Loader extends AbstractService
{
    /**
     * Initializes the loader
     */
    public function init()
    {
        $di = $this->getDi();
        $eventsManager = $this->getEventsManager();

        $modules = $di->get('modules');
        foreach ($modules as $module => $enabled) {
            if (!$enabled) {
                continue;
            }
            $modulesNamespaces[ucfirst($module)] = $this->_config->application->modulesDir . ucfirst($module);
        }

        $modulesNamespaces['Engine'] = $this->_config->application->engineDir;
        $modulesNamespaces['Library'] = $this->_config->application->librariesDir;

        $loader = new \Phalcon\Loader();
        $loader->registerNamespaces($modulesNamespaces);

        if ($this->_config->application->debug && $this->_config->installed) {
            $eventsManager->attach('loader', function ($event, $loader, $className) use ($di) {
                if ($event->getType() == 'afterCheckClass') {
                    $di->get('logger')->error("Can't load class '" . $className . "'");
                }
            });
            $loader->setEventsManager($eventsManager);
        }

        $loader->register();

        $di->set('loader', $loader);
    }
} 