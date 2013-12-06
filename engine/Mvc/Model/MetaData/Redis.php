<?php
/**
 * @namespace
 */
namespace Engine\Mvc\Model\MetaData;

use Engine\Cache\Backend\Redis as CacheBackend;
use Phalcon\Cache\Frontend\Data as CacheFrontend;
use Phalcon\Mvc\Model\Exception;

/**
 * \Engine\Mvc\Model\MetaData\Redis
 *
 * Redis adapter for \Phalcon\Mvc\Model\MetaData
 */
class Redis extends Base
{

    /**
     * Redis backend instance.
     *
     * @var \Engine\Cache\Backend\Redis
     */
    protected $redis = null;

    /**
     * {@inheritdoc}
     *
     * @param  null|array $options
     * @throws \Phalcon\Mvc\Model\Exception
     */
    public function __construct($options = null)
    {
        if (is_array($options)) {
            if (!isset($options['redis'])) {
                throw new Exception('Parameter "redis" is required');
            }
        } else {
            throw new Exception('No configuration given');
        }

        parent::__construct($options);
    }

    /**
     * {@inheritdoc}
     *
     * @return \Engine\Cache\Backend\Redis
     */
    protected function getCacheBackend()
    {
        if (null === $this->redis) {
            $this->redis = new CacheBackend(
                new CacheFrontend(['lifetime' => $this->options['lifetime']]),
                [
                    'redis' => $this->options['redis'],
                ]
            );
        }

        return $this->redis;
    }

}
