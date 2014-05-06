<?php
/**
 * @namespace
 */
namespace Engine\Application\Service;

use Engine\Application\Service\AbstractService;

/**
 * Class Database
 *
 * @category   Engine
 * @package    Application
 * @subpackage Service
 */
class Database extends AbstractService
{
    /**
     * Initializes the database and metadata adapter
     */
    public function init()
    {
        $di = $this->getDi();
        $eventsManager = $this->getEventsManager();
        $config = $this->_config;

        $adapter = $this->_getDatabaseAdapter($config->database->adapter);
        if (!$adapter) {
            throw new \Engine\Exception("Database adapter '{$config->database->adapter}' not exists!");
        }
        $connection = new $adapter([
            "host" => $this->_config->database->host,
            "username" => $this->_config->database->username,
            "password" => $this->_config->database->password,
            "dbname" => $this->_config->database->dbname,
            "options" => [
                \PDO::ATTR_EMULATE_PREPARES => false
            ]
        ]);

        if (!$config->application->debug && $config->database->useCache) {
            if ($di->offsetExists('modelsCache')) {
                //$connection->setCache($di->get('modelsCache'));
            }
        }

        if ($config->application->debug) {
            // Attach logger & profiler
            $logger = new \Phalcon\Logger\Adapter\File($config->application->logger->path."db.log");
            $profiler = new \Phalcon\Db\Profiler();

            $eventsManager->attach('db', function ($event, $connection) use ($logger, $profiler) {
                if ($event->getType() == 'beforeQuery') {
                    $statement = $connection->getSQLStatement();
                    $logger->log($statement, \Phalcon\Logger::INFO);
                    $profiler->startProfile($statement);
                }
                if ($event->getType() == 'afterQuery') {
                    //Stop the active profile
                    $profiler->stopProfile();
                }
            });

            if ($this->_config->application->profiler && $di->has('profiler')) {
                $di->get('profiler')->setDbProfiler($profiler);
            }
            $connection->setEventsManager($eventsManager);
        }

        $di->set('db', $connection);

        if (isset($config->database->useAnnotations) && $config->database->useAnnotations) {
            $di->set('modelsManager', function () use ($config, $eventsManager) {
                $modelsManager = new \Phalcon\Mvc\Model\Manager();
                $modelsManager->setEventsManager($eventsManager);

                //Attach a listener to models-manager
                $eventsManager->attach('modelsManager', new \Engine\Model\AnnotationsInitializer());

                return $modelsManager;
            }, true);
        }

        /**
         * If the configuration specify the use of metadata adapter use it or use memory otherwise
         */
        $service = $this;
        $di->set('modelsMetadata', function () use ($config, $service) {
            if ((!$config->application->debug || $config->application->useCachingInDebugMode) && isset($config->metadata)) {
                $metaDataConfig = $config->metadata;
                $metadataAdapter = $service->_getMetaDataAdapter($metaDataConfig->adapter);
                if (!$metadataAdapter) {
                    throw new \Engine\Exception("MetaData adapter '{$metaDataConfig->adapter}' not exists!");
                }
                $metaData = new $metadataAdapter($config->metadata->toArray());
            } else {
                $metaData = new \Phalcon\Mvc\Model\MetaData\Memory();
            }
            if (isset($config->database->useAnnotations) && $config->database->useAnnotations) {
                $metaData->setStrategy(new \Engine\Model\AnnotationsMetaDataInitializer());
            }

            return $metaData;
        }, true);
    }

    /**
     * Return metadata adapter full class name
     *
     * @param string $name
     * @return string
     */
    protected function _getMetaDataAdapter($name)
    {
        $adapter = '\Engine\Mvc\Model\MetaData\\'.ucfirst($name);
        if (!class_exists($adapter)) {
            $adapter = '\Phalcon\Mvc\Model\MetaData\\'.ucfirst($name);
            if (!class_exists($adapter)) {
                return false;
            }
        }

        return $adapter;
    }

    /**
     * Return database adapter full class name
     *
     * @param string $name
     * @return string
     */
    protected function _getDatabaseAdapter($name)
    {
        $adapter = '\Engine\Db\Adapter\\'.ucfirst($name);
        if (!class_exists($adapter)) {
            $adapter = '\Phalcon\Db\Adapter\\'.ucfirst($name);
            if (!class_exists($adapter)) {
                return false;
            }
        }

        return $adapter;
    }
} 