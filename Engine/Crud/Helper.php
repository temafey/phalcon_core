<?php
/**
 * @namespace
 */
namespace Engine\Crud;

/**
 * Class Helper
 *
 * @category   Engine
 * @package    Crud
 * @subpackage Helper
 */
abstract class Helper extends \Phalcon\Tag
{
    /**
     * Crud helper end tag
     *
     * @return string
     */
    static public function endTag()
    {
        return '';
    }

    /**
     * Return
     * @return string
     */
    static public function getSeparator()
    {
        return PHP_EOL;
    }
} 