<?php
/**
 * @namespace
 */
namespace Engine\Search\Elasticsearch;

use Elastica\Index as ElIndex;

/**
 * Class Index
 *
 * @category    Engine
 * @package     Search
 * @subcategory Elasticsearch
 */
class Index extends ElIndex implements
    \Phalcon\Events\EventsAwareInterface,
    \Phalcon\DI\InjectionAwareInterface
{
    use \Engine\Tools\Traits\DIaware,
        \Engine\Tools\Traits\EventsAware;

    /**
     * Returns a type object for the current index with the given name
     *
     * @param  string $type Type name
     * @return \Elastica\Type Type object
     */
    public function getType($type)
    {
        $type = new Type($type);
        $type->setAdapter($this->_client);

        return $type;
    }
}