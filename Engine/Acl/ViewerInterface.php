<?php
/**
 * @namespace
 */
namespace Engine\Acl;

/**
 * Interface ViewerInterface
 *
 * @category   Module
 * @package    Core
 * @subpackage Model
 */
interface ViewerInterface
{
    /**
     * Retuirn role name
     *
     * @return string
     */
    public function getRole();
} 