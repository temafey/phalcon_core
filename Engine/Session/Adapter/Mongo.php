<?php
/**
 * @namespace
 */
namespace Engine\Session\Adapter;

use Phalcon\Session\Adapter,
	Phalcon\Session\AdapterInterface,
	Phalcon\Session\Exception;

/**
 * Class Mongo
 *
 * @category    Engine
 * @package     Session
 * @subpackege  Adapter
 */
class Mongo extends Adapter implements AdapterInterface
{

	/**
	 * Phalcon\Session\Adapter\Mongo constructor
	 *
	 * @param array $options
	 */
	public function __construct($options=null)
	{

		if (!isset($options['collection'])) {
			throw new Exception("The parameter 'collection' is required");
		}

		session_set_save_handler(
			array($this, 'open'),
			array($this, 'close'),
			array($this, 'read'),
			array($this, 'write'),
			array($this, 'destroy'),
			array($this, 'gc')
		);

		parent::__construct($options);
	}


	public function open()
	{
		return true;
	}

	public function close()
	{
		return false;
	}

	/**
	 * Reads the data from the table
	 *
	 * @param string $sessionId
	 * @return string
	 */
	public function read($sessionId)
	{
		$options = $this->getOptions();

		$sessionData = $options['collection']->findOne(array('session_id' => $sessionId));
		if (is_array($sessionData)) {
			return $sessionData['data'];
		}

		return null;
	}

	/**
	 * Writes the data to the table
	 *
	 * @param string $sessionId
	 * @param string $data
	 */
	public function write($sessionId, $data)
	{
		$options = $this->getOptions();

		$sessionData = $options['collection']->findOne(array('session_id' => $sessionId));
		if (is_array($sessionData)) {
			$sessionData['data'] = $data;
		} else {
			$sessionData = array('session_id' => $sessionId, 'data' => $data);
		}

		$options['collection']->save($sessionData);

	}

	/**
	 * Destroyes the session
	 *
	 */
	public function destroy()
	{
		$options = $this->getOptions();
		$sessionData = $options['collection']->findOne(array('session_id' => session_id()));
		if (is_array($sessionData)) {
			$options['collection']->remove($sessionData);
		}
	}

	/**
	 * Performs garbage-collection on the session table
	 *
	 */
	public function gc()
	{

	}

}