<?php
/**
 * @namespace
 */
namespace Engine\Tools\Traits;

/**
 * Trait events aware.
 *
 * @category   Engine
 * @package    Tools
 */
trait EventsAware
{
	/**
	 * Events manager
	 * @var \Phalcon\Events\ManagerInterface
	 */
	protected $_eventsManager;

	/**
	 * Sets the events manager
	 * 
	 * @param \Phalcon\Events\ManagerInterface $eventsManager
	 * @return void
	 */
    public function setEventsManager($eventsManager)
    {
        $this->_eventsManager = $eventsManager;
    }

    /**
	 * Returns the internal event manager
	 * 
	 * @return \Phalcon\Events\ManagerInterface
     */
    public function getEventsManager()
    {
        return $this->_eventsManager;
    }
	
}