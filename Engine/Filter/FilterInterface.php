<?php
/**
 * @namespace
 */
namespace Engine\Filter;

/**
 * Interface FilterInterface
 *
 * @category    Engine
 * @package     Filter
 */
interface FilterInterface
{
    /**
     * Filter value
     *
     * @param $value
     * @return mixed
     */
    public function filter($value);
}