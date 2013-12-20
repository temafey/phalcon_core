<?php
/**
 * @namespace
 */
namespace Engine\Crud\Decorator;

use	Engine\Crud\Grid,
    Engine\Crud\Grid\Filter,
    Engine\Crud\Form;

/**
 * Class Factory for grid decorators.
 *
 * @category   Engine
 * @package    Crud
 * @subpackage Decorator
 */
class Decorator 
{
    /**
     * Factory for \Engine\Crud\Decorator classes.
     *
     * @param  \Engine\Crud\Form\Field|\Engine\Crud\Form|\Engine\Crud\Grid $element
     * @param  mixed $config
     * @return \Engine\Crud\Decorator
     */
    public static function factory($element, array $config = [])
    {
        /*
         * Verify that an decorator name has been specified.
         */
    	if (!isset($config['decorator'])) {
            throw new \RuntimeException("Decorator class name not set");
        }
    	if ($config['decorator'] == '') {
        	throw new \RuntimeException("Empty decorator decorator class name in config options array");
        }
        $decoratorName = $config['decorator'];
        unset($config['decorator']);

        //$decoratorModel = $config['template'];
        //unset($config['template']);
        
        /*
         * Decorator full decorator class name
         */
        $decoratorNamespace = self::getDecoratorNamespace($element);
        if (isset($config['namespace'])) {
            if ($config['namespace'] != '') {
                $decoratorNamespace = $config['namespace'];
            }
            unset($config['namespace']);
        }

        $decoratorName = $decoratorNamespace.'\\'.ucfirst($decoratorName);
        
        /*
         * Load the decorator class.  This throws an exception
         * if the specified class cannot be loaded.
         */
        if (!class_exists($decoratorName)) {
            throw new \Engine\Exception("FAILED TO FIND $decoratorName");
        }

        /*
         * Create an instance of the decorator class.
         * Pass the config to the decorator class constructor.
         */
        $decorator = new $decoratorName($config);
        $decorator->setElement($element);

        /*
         * Verify that the object created is a descendent of the abstract decorator type.
         */
        if (!$decorator instanceof \Engine\Crud\Decorator) {
            throw new \RuntimeException("Decorator class '$decoratorName' does not implements Crud\Decorator\AbstractDecorator");
        }

        return $decorator;
    }
    
    /**
     * Return decorator namespace
     * 
     * @param mixed $object
     * @return string
     */
    static function getDecoratorNamespace($object)
    {
    	if($object instanceof Grid) {
    		return '\Engine\Crud\Decorator\Grid';
    	} elseif($object instanceof Form) {
    		return '\Engine\Crud\Decorator\Form';
    	} elseif($object instanceof Filter) {
            return '\Engine\Crud\Decorator\Filter';
        } else {
    		throw new \Engine\Exception("Decorator object '".get_class($object)."' not instance");
    	}
    }
}