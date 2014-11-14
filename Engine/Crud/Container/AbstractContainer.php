<?php
/**
 * @namespace
 */
namespace Engine\Crud\Container;

/**
 * Class AbstractContainer.
 *
 * @category   Engine
 * @package    Crud
 * @subpackage Container
 */
abstract class AbstractContainer implements
    \Phalcon\Events\EventsAwareInterface,
    \Phalcon\DI\InjectionAwareInterface
{
    use \Engine\Tools\Traits\DIaware,
        \Engine\Tools\Traits\EventsAware;

	const MODEL          = 'model';
    const ADAPTER	     = 'adapter';
	const JOINS          = 'joins';
	const CONDITIONS	 = 'conditions';
	
	/**
	 * Database model
	 * @var \Engine\Mvc\Model
	 */
	protected $_model;

    /**
     * Database model adapter
     * @var string|array
     */
    protected $_adapter;
	
	/**
	 * Joins to database
	 * @var array
	 */
	protected $_joins = [];
	
	/**
	 * Container conditions
	 * @var array
	 */
	protected $_conditions = [];
	
	/**
	 * Set container options
	 * 
	 * @param array $options
	 * @return \Engine\Crud\Container\AbstractContainer
	 */
	public function setOptions(array $options)
	{
        if (isset($options[self::ADAPTER])) {
            $this->setAdapter($options[self::ADAPTER]);
        }
        if (isset($options[self::CONDITIONS])) {
            $this->setConditions($options[self::CONDITIONS]);
        }
        if (isset($options[self::MODEL])) {
            $this->setModel($options[self::MODEL]);
        }
        if (isset($options[self::JOINS])) {
            $this->setJoinModels($options[self::JOINS]);
        }
        
        return $this;
	}
	
	/**
	 * Return database model
	 * 
	 * @return \Engine\Mvc\Model
	 */
	public function getModel()
	{
		return $this->_model;
	}

    /**
     * Return database model adapter
     *
     * @return \Engine\Mvc\Model
     */
    public function getAdapter()
    {
        return $this->_adapter;
    }
	
	/**
	 * Set container conditions
	 * 
	 * @param array|string $conditions
	 * @return \Engine\Crud\Container\AbstractContainer
	 */
	public function setConditions($conditions)
	{
		if (null === $conditions || $conditions === false) {
			return false;
		}
		if (!is_array($conditions)) {
			$conditions = array($conditions);
		}
		foreach ($conditions as $cond) {
			if ($cond == "") {
				continue;
			}
			$this->_conditions[] = $cond;
		}
		
		return $this;
	}
	
	/**
	 * Set primary model
	 * 
	 * @param string|array $model
	 * @return void
	 */
	abstract public function setModel($model = null);

    /**
     * Set model adapter
     *
     * @param string|object $model
     * @return void
     */
    abstract public function setAdapter($adapder = null);
	
	/**
	 * Set join models
	 * 
	 * @param array|string $models
	 * @return void
	 */
	abstract public function setJoinModels($models);
}