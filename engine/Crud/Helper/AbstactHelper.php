<?php
/**
 * @namespace
 */
namespace Engine\Crud\Helper;

/**
 * Class AbstactHelper
 *
 * @category   Engine
 * @package    Crud
 * @subpackage Helper
 */
abstract class AbstactHelper extends \Phalcon\Tag
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
} 