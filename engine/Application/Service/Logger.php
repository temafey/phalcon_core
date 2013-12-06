<?php
/**
 * @namespace
 */
namespace Engine\Application\Service;

use Engine\Application\Service\AbstractService;

/**
 * Class the logger
 *
 * @category   Engine
 * @package    Application
 * @subpackage Service
 */
class Logger extends AbstractService
{
    /**
     * Initializes the logger
     */
    public function init()
    {
        $di = $this->getDi();
        $eventsManager = $this->getEventsManager();

        $config = $this->_config;
        if ($config->application->logger->enabled) {// && $config->installed) {
            $di->set('logger', function () use ($config) {
                $logger = new \Phalcon\Logger\Adapter\File($config->application->logger->path . "main.log");
                $formatter = new \Phalcon\Logger\Formatter\Line($config->application->logger->format);
                $logger->setFormatter($formatter);
                return $logger;
            });
        } else {
            $di->set('logger', function () use ($config) {
                $logger = new \Phalcon\Logger\Adapter\Syslog($config->application->logger->project, [
                    'option' => LOG_NDELAY,
                    'facility' => LOG_DAEMON
                ]);
                return $logger;
            });
        }
    }
} 