<?php
/**
 * @namespace
 */
namespace Engine\Crud\Helper\Tools;

/**
 * Trait Requires
 *
 * @category    Engine
 * @package     Crud
 * @subcategory Helper
 */
trait Requires
{

    /**
     * List of classes that have to be loaded before instantiating this class
     * @array
     */
    protected static $_requires = [];

    /**
     * Set list of classes that have to be loaded before instantiating this class
     *
     * @param array|string $requires
     * @return void
     */
    public static function setRequires($requires)
    {
        if (!is_array($requires)) {
            $requires = [$requires];
        }
        static::$_requires = $requires;
    }

    /**
     * Add list of classes that have to be loaded before instantiating this class
     *
     * @param array|string $requires
     * @return void
     */
    public static function addRequires($requires)
    {
        if (!is_array($requires)) {
            $requires = [$requires];
        }
        foreach ($requires as $require) {
            if (in_array($require, static::$_requires)) {
                continue;
            }
            static::$_requires[] = $require;
        }
    }

    /**
     * Return list of classes that have to be loaded before instantiating this class
     *
     * @return array|string
     */
    public static function getRequires($nomilize = false)
    {
        if (!$nomilize) {
           return static::$_requires;
        }

        $requires = [];
        foreach (static::$_requires as $require) {
            $requires[] = '"'.str_replace('"', '\"', $require).'"';
        }

        return implode(",", $requires);
    }

}