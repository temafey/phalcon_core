<?php
/**
 * @namespace
 */
namespace Engine\Crud\Tools;

use Engine\Crud\Decorator\Decorator;

/**
 * Trait render.
 *
 * @category   Engine
 * @package    Crud
 * @subpackage Decorator
 */
trait Renderer
{
	/**
	 * Decorator
	 * @var \Engine\Crud\Decorator
	 */
	protected $_decorator = null;

    /**
     * Decorator namespace
     * @var string
     */
    protected $_decoratorNamespace = '';

	/**
	 * Decorator helpers
	 * @var array
	 */
	protected $_helpers = [];

	/**
	 * Clear all helpers
	 *
	 * @return \Engine\Crud\Tools\Renderer
	 */
	public function clearHelpers()
	{
		$this->_helpers = [];
		return $this;
	}
	
	/**
	 * Set helpers
	 *
	 * @param array $helpers
	 * @return \Engine\Crud\Tools\Renderer
	 */
	public function setHelpers(array $helpers)
	{
		$this->clearHelpers();
		$this->addHelpers($helpers);
		return $this;
	}
	
	/**
	 * Add helpers
	 *
	 * @param array $helpers
	 * @return \Engine\Crud\Tools\Renderer
	 */
	public function addHelpers(array $helpers)
	{
		foreach ($helpers as $helper) {
			$this->addHelper($helper);
		}
	
		return $this;
	}
	
	/**
	 * Add helper
	 *
	 * @param string $helper
	 * @return \Engine\Crud\Tools\Renderer
	 */
	public function addHelper($helper)
	{
		$helper = $this->_normalizeHelper($helper);
		if (!in_array($helper, $this->_helpers)) {
			$this->_helpers[] = $helper;
		}
	
		return $this;
	}
	
	/**
	 * Return decorator helpers
	 *
	 * @return array
	 */
	public function getHelpers()
	{
		return $this->_helpers;
	}
	
	/**
	 * Normalize helper name
	 *
	 * @param string $helper
	 * @return string
	 */
	public function _normalizeHelper($helper)
	{
		return ucfirst(trim($helper));
	}
	
	/**
	 * Render object
	 *
	 * @return string
	 */
	public function render($content = '')
	{
        $this->_beforeRender();
		$decorator = $this->getDecorator();
		$content = $decorator->render($content);
	
		return $content;
	}
	
	/**
	 * Instantiate a decorator based on class name
	 *
	 * @return \Engine\Crud\Decorator
	 */
	public function getDecorator()
	{
		if (null === $this->_decorator) {
            throw new \Engine\Exception('Decorator not set');
        }

		$config = [];
		$config['decorator'] = $this->_decorator;
        $config['namespace'] = $this->_decoratorNamespace;
        $decorator = Decorator::factory($this, $config);

        return $decorator;
	}
}