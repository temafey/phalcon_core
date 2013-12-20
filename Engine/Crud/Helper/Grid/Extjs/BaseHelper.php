<?php
/**
 * @namespace
 */
namespace Engine\Crud\Helper\Grid\Extjs;

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
     * @var boolen
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
     * @param \Engine\Crud\Grid\Extjs $grid
     * @return string
     */
    public static function init(\Engine\Crud\Grid\Extjs $grid)
    {
        static::$_module = $grid->getModuleName();
        static::$_prefix = $grid->getKey();
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
        return static::$_module.".view.grid.".static::$_prefix;
    }

    /**
     * Return window object name
     *
     * @return string
     */
    public static function getWinName()
    {
        return static::$_module.".view.win.".static::$_prefix;
    }

    /**
     * Return model object name
     *
     * @return string
     */
    public static function getModelName()
    {
        return static::$_module.".model.".static::$_prefix;
    }

    /**
     * Return store object name
     *
     * @return string
     */
    public static function getStoreName()
    {
        return static::$_module.".store.".static::$_prefix;
    }

    /**
     * Return grid object name
     *
     * @return string
     */
    public static function getStoreLocalName()
    {
        return static::$_module.".store.".static::$_prefix."Local";
    }

    /**
     * Return controller object name
     *
     * @return string
     */
    public static function getControllerName()
    {
        return static::$_module.".controller.".static::$_prefix;
    }

    /**
     * Is create js file prototype
     *
     * @return boolen
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