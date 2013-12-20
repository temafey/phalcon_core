<?php
/**
 * @namespace
 */
namespace Engine\Crud\Decorator\Form;

use Crud\Decorator\Decorator,
	Crud\Form\Form;

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
        
        $separator     = $this->getSeparator();
        $helpers = $element->getHelpers();
        if (empty($helpers)) {
        	$helpers = $this->getDefaultHelpers();
        }
        $attribs['id'] = $element->getId();
        $action = $this->_form->getAction();        
        $attribs['action'] = ($id) ? '/cms/form/index?form='.$action.'&id='.$id : '/cms/form/index?form='.$action;
        
        $options = $element->getOptions();
        
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
	 * Return default helpers
	 *
	 * @return array
	 */
	public function getDefaultHelpers()
	{
		$helpers = array(
			'DataStore',
			'Form'
		);
		return $helpers;
	}
}