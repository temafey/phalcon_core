<?php
/**
 * @namespace
 */
namespace Engine\Application\Service;

use Engine\Application\Service\AbstractService;

/**
 * Class Annotations
 *
 * @category   Engine
 * @package    Application
 * @subpackage Service
 */
class Annotations extends AbstractService
{
    /**
     * Initializes Annotations system
     */
    public function init()
    {
        $di = $this->getDi();
        $eventsManager = $this->getEventsManager();

        $config = $this->_config;
        $di->set('annotations', function () use ($config) {
            if (!$config->application->debug && isset($config->annotations)) {
                $annotationsAdapter = '\Phalcon\Annotations\Adapter\\' . $config->annotations->adapter;
                $adapter = new $annotationsAdapter($config->annotations->toArray());
            } else {
                $adapter = new \Phalcon\Annotations\Adapter\Memory();
            }

            return $adapter;
        }, true);
    }
} 