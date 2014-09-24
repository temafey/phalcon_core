<?php
/**
 * @namespace
 */
namespace Engine\Search\Elasticsearch;

use Elastica\Client as ElCient;

/**
 * Class Type
 *
 * @category    Engine
 * @package     Search
 * @subcategory Elasticsearch
 */
class Client extends ElCient implements
    \Phalcon\Events\EventsAwareInterface,
    \Phalcon\DI\InjectionAwareInterface
{
    use \Engine\Tools\Traits\DIaware,
        \Engine\Tools\Traits\EventsAware;

    /**
     * Returns the index for the given connection
     *
     * @param  string         $name Index name to create connection to
     * @return \Elastica\Index Index for the given name
     */
    public function getIndex($name = null)
    {
        if (null === $name) {
            $name = $this->getConfig('index');
        }
        return new Index($this, $name);
    }
}