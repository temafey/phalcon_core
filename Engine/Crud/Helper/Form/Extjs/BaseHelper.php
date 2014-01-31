<?php
/**
 * @namespace
 */
namespace Engine\Crud\Helper\Form\Extjs;

use Engine\Crud\Helper\Grid\Extjs\BaseHelper as Base,
    Engine\Crud\Form\Extjs as Form;

/**
 * Class html form helper
 *
 * @category   Engine
 * @package    Crud
 * @subpackage Helper
 */
class BaseHelper extends Base
{

    /**
     * Render filter form field
     *
     * @param \Engine\Crud\Form\Field $field
     * @return string
     */
    public static function renderField(\Engine\Crud\Form\Field $field)
    {
        $helperName = self::getFieldHelper($field);
        $helper = \Engine\Crud\Decorator\Helper::factory($helperName, $field);

        $elementContent = call_user_func_array([$helper['helper'], '_'], [$helper['element']]);
        $elementContent .= call_user_func([$helper['helper'], 'endTag']);

        return $elementContent;
    }

    /**
     * Return extjs form field helper name
     *
     * @param \Engine\Crud\Form\Field $field
     * @return string
     */
    public static function getFieldHelper(\Engine\Crud\Form\Field $field)
    {
        $reflection = new \ReflectionClass(get_class($field));
        $name = $reflection->getShortName();

        return 'extjs\\'.$name;
    }
}