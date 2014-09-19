<?php
/**
 * @namespace
 */
namespace Engine\Tools\Traits;
	
/**
 * Trait resource.
 *
 * @category   Engine
 * @package    Tools
 */
trait Resource 
{		
	/**
	 * Prefix for auroload methods in object
	 * @var string
	 */
	protected $_autoloadMethodPrefix = null;
	
	/**
	 * Prefix exceptions for auroload methods in object
	 * @var string
	 */
	protected $_autoloadMethodPrefixException = [];

	/**
     * @var array Internal resource methods (resource/method pairs)
     */
    protected $_classResources = [];
	
    /**
     * @var array Initializers that have been run
     */
    protected $_run = [];
	
    /**
     * @var array Initializers that have been started but not yet completed (circular dependency detection)
     */
    protected $_started = [];
    
    /**
     * @var array Class methods
     */
    protected $_methods = [];
    
    /**
	 * Get all object method
	 * 
	 * @return void
     */
    protected function _initResource()
    {
    	$this->_methods = get_class_methods($this);
    }
    
    /**
     * Execute all _setup* methods in class
     */
    protected function _runResourceMethods()
    {
    	foreach ($this->getClassResourceNames() as $resource) {
        	$this->_executingResource($resource);
        }
    }
	
    /**
     * Get $_autoloadMethodPrefix.
     * @return string
     */
    public function getAutoloadMethodPrefix()
    {
    	return $this->_autoloadMethodPrefix;        
    }
    
    /**
     * Get $_autoloadMethodPrefixException
     * @return string
     */
    public function getAutoloadMethodPrefixException()
    {
    	return $this->_autoloadMethodPrefixException;        
    }
    
    /**
     * Set $_autoloadMethodPrefix
     * @param $prefix string
     */
    public function setAutoloadMethodPrefix($prefix)
    {
        $this->_autoloadMethodPrefix = $prefix;
        return $this;
    }
    
    /**
     * Set $_autoloadMethodPrefixException.
     * @return \Tools\Resource
     */
    public function setAutoloadMethodPrefixException($prefixes)
    {
    	$this->_autoloadMethodPrefixException = [];
    	if (!is_array($prefixes)) {
    		$prefixes = array($prefixes);
    	}
    	foreach ($prefixes as &$prefix) {
        	$this->addAutoloadMethodPrefixException($prefix);
    	}
    	
        return $this;
    }

    /**
     * Add prefix exception.
     * 
     * @param $prefix string
     * @return \Tools\Resource
     */
    public function addAutoloadMethodPrefixException($prefix)
    {
    	if (!in_array($prefix, $this->_autoloadMethodPrefixException)) {
    		$this->_autoloadMethodPrefixException[] = $prefix;
    	}
    	
    	return $this; 
    }
    
    /**
     * Execute a resource.
     *
     * Checks to see if the resource has already been run. If not, it searches
     * first to see if a local method matches the resource, and executes that.
     * If not, it checks to see if a plugin resource matches, and executes that
     * if found.
     *
     * Finally, if not found, it throws an exception.
     *
     * @param  string $resource
     * @return void
     * @throws Engine_Layout_Cell_Exception When resource not found
     */
    protected function _executingResource($resource)
    {
        $resourceName = strtolower($resource);
        
        if (in_array($resourceName, $this->_run)) {
            return;
        }

        if (!empty($this->_started[$resourceName])) {
            throw new \Exception('Circular resource dependency detected ');
        }

        $classResources = $this->getClassResources();

        if (isset($classResources[$resourceName])) {
            $this->_started[$resourceName] = true;
            $method = $classResources[$resourceName];            
            $return = $this->$method();
            unset($this->_started[$resourceName]);
            $this->_markRun($method);

            /*if (null !== $return) {
                $this->getContainer()->{$resourceName} = $return;
            }*/

            return;
        }
    }
    
    /**
     * Get class resources (as resource/method pairs)
     *
     * Uses get_class_methods() by default, reflection on prior to 5.2.6,
     * as a bug prevents the usage of get_class_methods() there.
     *
     * @return array
     */
    public function getClassResources()
    {
    	$methodPrefix = $this->getAutoloadMethodPrefix();
    	
        if (!isset($this->_classResources[$methodPrefix])) {
            $this->_classResources[$methodPrefix] = [];
            $prefixLen = strlen($methodPrefix);
            
            foreach ($this->_methods as $i => &$method) {
                if ($methodPrefix != $method && $methodPrefix === substr($method, 0, $prefixLen)) {                	
                	if (!$this->isException($method)) {
                		unset($this->_methods[$i]);
                    	$this->_classResources[$methodPrefix][ strtolower(substr($method, $prefixLen)) ] = $method;
                	}
                }
            }
        }
        
		$this->addAutoloadMethodPrefixException($methodPrefix);
		
        return $this->_classResources[$methodPrefix];
    }
	
    /**
     * Check is method name is exception
     * 
     * @param string $method
     * @return bool
     */
    public function isException($method)
    {
    	$methodExceptions = $this->getAutoloadMethodPrefixException();
    	
    	foreach ($methodExceptions as &$exception) {
    		$exceptionLen = strlen($exception);
    		if ($exception === substr($method, 0, $exceptionLen)) {
    			return true;
    		}
    	}
    	
    	return false;
    }
    
    
    /**
     * Get class resource names
     *
     * @return array
     */
    public function getClassResourceNames()
    {
        $resources = $this->getClassResources();
        return array_keys($resources);
    }
    
    /**
     * Mark a resource as having run
     *
     * @param  string $resource
     * @return void
     */
    protected function _markRun($resource)
    {
        if (!in_array($resource, $this->_run)) {
            $this->_run[] = $resource;
        }
    }
}