<?php
/**
 * @namespace
 */
namespace Engine\Session\Adapter;

use Phalcon\Session\Adapter\Files as Adapter,
	Phalcon\Session\AdapterInterface,
	Phalcon\Session\Exception;

/**
 * Class Files
 *
 * @category    Engine
 * @package     Session
 * @subpackege  Adapter
 */
class Files extends Adapter implements AdapterInterface
{

	/**
	 * Phalcon\Session\Adapter\Files constructor
	 *
	 * @param array $options
	 */
	public function __construct($options=null)
	{
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

