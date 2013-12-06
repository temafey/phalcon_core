<?php
/**
 * @namespace
 */
namespace Engine\Application\Service;

use Engine\Application\Service\AbstractService;

/**
 * Class Flash
 *
 * @category   Engine
 * @package    Application
 * @subpackage Service
 */
class Flash  extends AbstractService
{
    /**
     * Initializes the flash messages
     */
    public function init()
    {
        $di = $this->getDi();
        $eventsManager = $this->getEventsManager();

        $di->set('flash', function () {
            $flash = new \Phalcon\Flash\Direct([
                'error' => 'alert alert-error',
                'success' => 'alert alert-success',
                'notice' => 'alert alert-info',
            ]);
            return $flash;
        });

        $di->set('flashSession', function () {
            $flash = new \Phalcon\Flash\Session([
                'error' => 'alert alert-error',
                'success' => 'alert alert-success',
                'notice' => 'alert alert-info',
            ]);
            return $flash;
        });
    }
} 