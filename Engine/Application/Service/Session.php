<?php
 /**
 * @namespace
 */
namespace Engine\Application\Service;

use Engine\Application\Service\AbstractService;

/**
 * Class Session
 *
 * @category   Engine
 * @package    Application
 * @subpackage Service
 */
class Session extends AbstractService
{
    /**
     * Initializes the session
     */
    public function init()
    {
        $di = $this->getDi();
        $eventsManager = $this->getEventsManager();

        if (!isset($this->_config->application->session)) {
            $session = new \Phalcon\Session\Adapter\Files();
        } else {
            $sessionAdapter = $this->_getSessioneAdapter($this->_config->application->session->adapter);
            if (!$sessionAdapter) {
                throw new \Engine\Exception("Session adapter '{$this->_config->application->session->adapter}' not exists!");
            }
            $sessionOptions = $this->_config->application->session->toArray();

            $session = new $sessionAdapter($sessionOptions);
        }
        $session->start();
        $di->set('session', $session, true);
    }

    /**
     * Return session adapter full class name
     *
     * @param string $name
     * @return string
     */
    protected function _getSessioneAdapter($name)
    {
        $adapter = '\Engine\Session\Adapter\\'.ucfirst($name);
        if (!class_exists($adapter)) {
            $adapter = '\Phalcon\Session\Adapter\\'.ucfirst($name);
            if (!class_exists($adapter)) {
                return false;
            }
        }

        return $adapter;
    }
} 