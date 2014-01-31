<?php
/**
 * @namespace
 */
namespace Engine\Crud\Helper\Grid\Extjs;

use Engine\Crud\Grid\Extjs as Grid;

/**
 * Class html grid helper
 *
 * @category   Engine
 * @package    Crud
 * @subpackage Helper
 */
class BaseHelper extends \Engine\Crud\Helper
{
    /**
     * Is create js file prototype
     * @var boolean
     */
    protected static $_createJs = false;

    /**
     * Application name
     * @var string
     */
    protected static $_module;

    /**
     * Object prefix
     * @var string
     */
    protected static $_prefix;

    /**
     * Init helper
     *
     * @param \Engine\Crud\Grid\Extjs|\Engine\Crud\Form\Extjs $element
     * @return string
     */
    public static function init($element)
    {
        static::$_module = $element->getModuleName();

        $prefix = explode("-", $element->getKey());
        foreach ($prefix as $i => &$word) {
            if ($i === 0) {
                continue;
            }
            $word = ucfirst($word);
        }
        static::$_prefix =implode("", $prefix);
    }

    /**
     * Return object name
     *
     * @return false
     */
    public static function getName()
    {
        return false;
    }

    /**
     * Return grid object name
     *
     * @return string
     */
    public static function getGridName()
    {
        return ucfirst(static::$_module).".view.".static::$_prefix.".Grid";
    }

    /**
     * Return form object name
     *
     * @return string
     */
    public static function getFormName()
    {
        return ucfirst(static::$_module).".view.".static::$_prefix.".Form";
    }

    /**
     * Return window object name
     *
     * @return string
     */
    public static function getWinName()
    {
        return ucfirst(static::$_module).".view.".static::$_prefix.".Win";
    }

    /**
     * Return model object name
     *
     * @return string
     */
    public static function getModelName()
    {
        return ucfirst(static::$_module).".model.".ucfirst(static::$_prefix);
    }

    /**
     * Return store object name
     *
     * @return string
     */
    public static function getStoreName()
    {
        return ucfirst(static::$_module).".store.".ucfirst(static::$_prefix);
    }

    /**
     * Return grid object name
     *
     * @return string
     */
    public static function getStoreLocalName()
    {
        return ucfirst(static::$_module).".store.".ucfirst(static::$_prefix)."Local";
    }

    /**
     * Return controller object name
     *
     * @return string
     */
    public static function getControllerName()
    {
        return ucfirst(static::$_module).".controller.".ucfirst(static::$_prefix);
    }

    /**
     * Is create js file prototype
     *
     * @return boolean
     */
    public static function createJs()
    {
        return static::$_createJs;
    }

    /**
     * Return js file path from name
     *
     * @return string
     */
    public static function getJsFilePath($name)
    {
        return str_replace(".", "/", $name).".js";
    }
}