<?php
/**
 * @namespace
 */
namespace Engine\Crud\Helper\Filter;

use Engine\Crud\Grid\Filter,
    Engine\Crud\Helper\Filter\Extjs\BaseHelper;

/**
 * Class grid filter helper
 *
 * @category   Engine
 * @package    Crud
 * @subpackage Helper
 */
class Extjs extends BaseHelper
{
    /**
     * Is create js file prototype
     * @var boolean
     */
    protected static $_createJs = true;

    /**
     * Generates a widget to show a html grid filter
     *
     * @param \Engine\Crud\Grid\Filter $filter
     * @return string
     */
    static public function _(Filter $filter)
    {
        $filter->initForm();
        $form = $filter->getForm();
        $title = $filter->getTitle();

        $code = "
        Ext.define('".static::getFilterName()."', {
            extend: 'Ext.ux.crud.Filter',
            alias: 'widget.".static::$_module.ucfirst(static::$_prefix)."Filter',
            title: '".$title."',

            bodyPadding: 5,
            autoScroll: true,
            waitMsgTarget: true,
            border: false,
            fieldDefaults: {
                labelAlign: 'right',
                labelWidth: 85,
                msgTarget: 'side'
            },
            defaultType: 'textfield',
            defaults: {
                width: 280
            },
            buttonAlign: 'left',
            ";

        /*$width = $form->getWidth();
        if ($width) {
            $code .= "width: ".$width.",
            ";
        }
        $height = $form->getHeight();
        if ($width) {
            $code .= "height: ".$height.",
            ";
        }*/

        $code .= "requires: [";
        $requires = [];

        $requires[] = "'Ext.form.field.*'";
        $requires[] = "'Ext.ux.crud.Filter'";
        $code .= implode(",", $requires);

        $code .= "],
            ";

        $code .= "itemId: '".static::$_module.ucfirst(static::$_prefix)."Filter',
            ";

        return $code;
    }

    /**
     * Return object name
     *
     * @return string
     */
    public static function getName()
    {
        return static::getFilterName();
    }

    /**
     * Crud helper end tag
     *
     * @return string
     */
    static public function endTag()
    {
        return "
        });";
    }
} 