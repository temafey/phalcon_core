<?php
/**
 * @namespace
 */
namespace Engine\Crud\Decorator\Filter;

use Engine\Crud\Decorator\AbstractDecorator as Decorator,
	Engine\Crud\Grid\Filter,
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

        foreach ($helpers as $i => $helper) {
            $helpers[$i] = Helper::factory($helper, $element);
        }

        $sections = [];
        foreach ($helpers as $helper) {
            $sections[] = call_user_func_array([$helper['helper'], '_'], [$helper['element']]);
        }

        foreach ($element->getFields() as $field) {
            $sections[] = $this->renderField($field);
        }

        $elementContent = implode($separator, $sections);
        foreach (array_reverse($helpers) as $helper) {
            $elementContent .= $sections[] = call_user_func([$helper['helper'], 'endTag']);
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
     * Render filter form field
     *
     * @param \Engine\Crud\Form\Field\AbstractField $field
     * @return string
     */
    public function renderField(\Engine\Crud\Grid\Filter\Field\AbstractField $field)
    {
        $helpers = $field->getHelpers();
        foreach ($helpers as $i => $helper) {
            $helpers[$i] = Helper::factory($helper, $field);
        }

        $sections = [];
        foreach ($helpers as $helper) {
            $sections[] = call_user_func_array([$helper['helper'], '_'], [$helper['element']]);
        };

        $separator = $this->getSeparator();
        $elementContent = implode($separator, $sections);

        foreach (array_reverse($helpers) as $helper) {
            $elementContent .= $sections[] = call_user_func([$helper['helper'], 'endTag']);
        }

        return $elementContent;
    }
	
	/**
	 * Return defualt helpers
	 * 
	 * @return array
	 */
	public function getDefaultHelpers()
	{
		$helpers = [
            'standart'
		];

		return $helpers;
	}
}