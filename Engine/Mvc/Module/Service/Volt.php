<?php
/**
 * @namespace
 */
namespace Engine\Mvc\Module\Service;

use Engine\Mvc\Module\Service\AbstractService,
    \Phalcon\Mvc\View\Engine\Volt as ViewEngine;

/**
 * Class Volt
 *
 * @category   Engine
 * @package    Mvc
 * @subpackage Moduler
 */
class Volt extends AbstractService
{
    /**
     * Initializes Volt engine
     */
    public function register()
    {
        $di = $this->getDi();
        $eventsManager = $this->getEventsManager();

        $config = $this->_config;

        $di->set('viewEngine', function ($view, $di) use ($config) {
            $volt = new ViewEngine($view, $di);
            $volt->setOptions([
                "compiledPath" => $config->application->view->compiledPath,
                "compiledExtension" => $config->application->view->compiledExtension,
                'compiledSeparator' => $config->application->view->compiledSeparator,
                'compileAlways' => $config->application->view->compileAlways
            ]);

            $compiler = $volt->getCompiler();
            $compiler->addFilter('dump', function ($resolvedArgs) {
                return 'var_dump('.$resolvedArgs.')';
            });

            return $volt;
        });
    }
} 