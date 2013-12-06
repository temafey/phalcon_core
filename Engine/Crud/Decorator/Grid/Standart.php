<?php
/**
 * @namespace
 */
namespace Engine\Crud\Decorator\Grid;

use Engine\Crud\Decorator\AbstractDecorator as Decorator,
	Engine\Crud\Grid\AbstractGrid as Grid,
    Engine\Crud\Decorator\Helper;

/**
 * Class Extjs decorator for grid.
 *
 * @category   Engine
 * @package    Crud
 * @subpackage Decorator
 */
class Standart extends Decorator
{	
	/**
     * Render an element
     *
     * @param  string $content
     * @return string
     * @throws \UnexpectedValueException if element or view are not registered
     */
	public function render($content = '')
	{
        $element = $this->getElement();

        $separator = $this->getSeparator();
        $helpers = $element->getHelpers();
        if (empty($helpers)) {
        	$helpers = $this->getDefaultHelpers();
        }
        $attribs['id'] = $element->getId();

        foreach ($helpers as $i => $helper) {
            $helpers[$i] = Helper::factory($helper, $element);
        }

        $sections = [];
        foreach ($helpers as $helper) {
            $sections[] = call_user_func_array([$helper['helper'], '_'], [$helper['element']]);
        }

        $elementContent = implode($separator, $sections);
        foreach (array_reverse($helpers) as $helper) {
            $elementContent .= call_user_func([$helper['helper'], 'endTag']);
        }

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
		$helpers = [
            'filter',
            'standart',
            'standart\Columns',
            'standart\Datastore',
            'standart\Paginator'
		];

		return $helpers;
	}
}