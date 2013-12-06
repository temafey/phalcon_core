<?php
/**
 * @namespace
 */
namespace Engine\Application\Service;

use Engine\Application\Service\AbstractService;

/**
 * Class Url
 *
 * @category   Engine
 * @package    Application
 * @subpackage Service
 */
class Url extends AbstractService
{
    /**
     * Initializes the baseUrl
     */
    public function init()
    {
        $di = $this->getDi();
        $eventsManager = $this->getEventsManager();

        /**
         * The URL component is used to generate all kind of urls in the
         * application
         */
        $url = new \Phalcon\Mvc\Url();
        $url->setBaseUri($this->_config->application->baseUri);
        $di->set('url', $url);
    }
} 