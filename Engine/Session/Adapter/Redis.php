<?php
/**
 * @namespace
 */
namespace Engine\Session\Adapter;

use Phalcon\Session\Adapter,
	Phalcon\Session\AdapterInterface,
	Phalcon\Session\Exception;

/**
 * Class Redis
 *
 * @category    Engine
 * @package     Session
 * @subpackege  Adapter
 */
class Redis extends Adapter implements AdapterInterface
{

	/**
	 * Phalcon\Session\Adapter\Redis constructor
	 *
	 * @param array $options
	 */
	public function __construct($options=null)
	{
		if(!isset($options['path'])){
			throw new Exception("The parameter 'save_path' is required");
		}

		ini_set('session.save_handler', 'redis');
        if (is_array($options['path'])) {
            if (!isset($options['path'][0])) {
                $path = $options['path'];
                unset($options['path']);
                $options['path'] = [$path];
            }
            $paths = [];
            foreach ($options['path'] as $val) {
                $path = 'tcp://';
                $path .= $val['host'];
                unset($val['host']);
                $path .= ":".$val['port'];
                unset($val['port']);
                if (!empty($val)) {
                    $path .= "?".http_build_query($val);
                }
                $paths[] = $path;
            }
            $options['path'] = implode(", ", $paths);
        }
		ini_set('session.save_path', $options['path']);
		
        if (isset($options['name'])) {
            ini_set('session.name', $options['name']);
        }
        if (isset($options['lifetime'])) {
            ini_set('session.gc_maxlifetime', $options['lifetime']);
        }
        if (isset($options['cookie_lifetime'])) {
            ini_set('session.cookie_lifetime', $options['cookie_lifetime']);
        }

		parent::__construct($options);
	}

}

