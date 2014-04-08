<?php
/**
 * @namespace
 */
namespace Engine;

use Phalcon\Mvc\Application as PhApplication;

/**
 * Class Application
 *
 * @category   Engine
 * @package    Application
 */
abstract class Application extends PhApplication
{
    /**
     * Default module name.
     *
     * @var string
     */
    public static $defaultModule = 'core';

    /**
     * Config path
     * @var string
     */
    protected $_configPath;

    /**
     * Loaders for different modes.
     *
     * @var array
     */
    protected $_services = [];

    /**
     * Application services namespace
     * @var string
     */
    protected $_serviceNamespace = '\Engine\Application\Service';
	
    /**
     * @var \Phalcon\Config
     */
    protected $_config;

    /**
     * Constructor
     */
    public function __construct()
    {
        if (empty($this->_configPath)) {
            $class = new \ReflectionClass($this);
            throw new \Engine\Exception('Application has no config path: '.$class->getFileName());
        }

        $loader = new \Phalcon\Loader();
        $loader->registerNamespaces(['Engine' => ROOT_PATH.'/engine']);
        $loader->register();

        // create default di
        $di = new \Phalcon\DI\FactoryDefault();

        // get config
        $this->_config = include_once(ROOT_PATH.$this->_configPath);

        // Store config in the Di container
        $di->setShared('config', $this->_config);

        parent::__construct($di);
    }

    /**
     * Runs the application, performing all initializations
     *
     * @return void
     */
    public function run()
    {
        $modules = $this->_config->get('modules');
        if (!$modules) {
            $modules = (object) [];
        }

        $di = $this->_dependencyInjector;
        $config = $this->_config;

        $di->set('modules', function () use ($modules) {
            return $modules;
        });

        // Set application event manager
        $eventsManager = new \Phalcon\Events\Manager();

        // Init services and engine system
        foreach ($this->_services as $serviceName) {
            $service = $this->_serviceNamespace."\\".ucfirst($serviceName);
            $service = new $service($di, $eventsManager, $config);
            if (!($service instanceof \Engine\Application\Service\AbstractService)) {
                throw new \Engine\Exception("Service '{$serviceName}' not instance of AbstractService");
            }
            $service->init();
        }

        // register enabled modules
        $enabledModules = [];
        if (!empty($modules)) {
            foreach ($modules as $module => $enabled) {
                if (!$enabled) {
                    continue;
                }
                $moduleName = ucfirst($module);
                $enabledModules[$module] = array(
                    'className' => $moduleName.'\Module',
                    'path' => ROOT_PATH.'/apps/modules/'.$moduleName.'/Module.php',
                );
            }

            if (!empty($enabledModules)) {
                $this->registerModules($enabledModules);
            }
        }

        // Set default services to the DI
        $this->setEventsManager($eventsManager);
        $di->setShared('eventsManager', $eventsManager);
        $di->setShared('app', $this);
    }

    /**
     * Return string content
     * @return string
     */
    public function getOutput()
    {
        return $this->handle()->getContent();
    }

    /**
     * Clear application cache
     */
    public function clearCache()
    {

    }
}