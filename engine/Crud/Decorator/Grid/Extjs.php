<?php
/**
 * @namespace
 */
namespace Engine\Crud\Decorator\Grid;

use Crud\Decorator\AbstractDecorator as Decorator,
	Crud\Grid\AbstractGrid as Grid;

/**
 * Class Extjs decorator for grid.
 *
 * @category   Engine
 * @package    Crud
 * @subpackage Decorator
 */
class Extjs extends Decorator
{	
	/**
     * Render an element
     *
     * @param  string $content
     * @return string
     * @throws \UnexpectedValueException if element or view are not registered
     */
	public function render($content)
	{
        if (null === $view) {
            throw new \UnexpectedValueException('ViewHelper decorator cannot render without a registered view object');
        }
        
        $element = $this->getElement();        
        $view = $element->getView();
        
        $separator = $this->getSeparator();
        $helpers = $element->getHelpers();
        if (empty($helpers)) {
        	$helpers = $this->getDefaultHelpers();
        }
        $attribs['id'] = $element->getId();
        
        $sections = array();
        foreach ($helpers as $helper) {
        	$helper = \ExtJs\Factory::getHelper($helper, $attribs, $options);
        	$sections[] = $helper->render();
        }
        $elementContent = implode($separator, $sections);
        
        switch ($this->getPlacement()) {
            case self::APPEND:
                return $content . $separator . $elementContent;
            case self::PREPEND:
                return $elementContent . $separator . $content;
            default:
                return $elementContent;
        }
	}
	
	/**
	 * Return defualt helpers
	 * 
	 * @return array
	 */
	public function getDefaultHelpers()
	{
		$helpers = array(
			'Grid',
			'DataStore',
			'Form'
		);
		return $helpers;
	}
}