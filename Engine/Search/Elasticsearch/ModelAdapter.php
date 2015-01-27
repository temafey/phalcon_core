<?php
/**
 * @namespace
 */
namespace Engine\Search\Elasticsearch;

/**
 * Elasticsearch model adapter interface.
 *
 * @category   Engine
 * @package    Crud
 * @subpackage Container
 */
interface ModelAdapter
{

    /**
     * Return search type name
     *
     * @return string
     */
    public function getSearchSource();

}