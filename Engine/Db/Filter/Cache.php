<?php
/**
 * @namespace
 */
namespace Engine\Db\Filter;

use \Engine\Mvc\Model\Query\Builder;

/**
 * Cache search filter
 *
 * @category   Engine
 * @package    Db
 * @subpackage Filter
 */ 
class Cache extends Search 
{
	/**
	 * Cache
	 * @var string|array
	 */
	protected $_cache;
	
	/**
	 * Fitler criteria
	 * @var string
	 */
	protected $_criteria;
	
	/**
	 * Constructor
	 * 
	 * @param string $value
	 * @param array $columns
	 * @param array|string $cache
	 * @param string $criteria
	 */
	public function __construct($value , $columns, $cache, $criteria = self::CRITERIA_BEGINS) 
	{
		parent::__construct($columns, $value);
		
		if (empty($cache)) {
			$this->_cache = false;
		} elseif (is_array($cache)) {
			$this->_cache = $cache;
		} else {
			$this->_cache = \Zend\Registry::get($cache);
		}
		$this->_criteria = $criteria;
		$this->_setValue();
	}

    /**
     * Apply filter to query builder
     *
     * @param \Engine\Mvc\Model\Query\Builder $dataSource
     * @return string
     */
    public function filterWhere(Builder $dataSource)
    {
		if (!$this->_cache) {
			return parent::filterWhere($dataSource);
		}
		if (count($this->_value) == 0) {
			return false;
		}
		if (count($this->_value) == 1) {
			$this->_value = $this->_value[0];
			return parent::filterWhere($dataSource);
		}
		
		$where = implode (",", $this->_value);
		$this->_columns = array_keys($this->_columns);
		
		return $this->_columns[0]." IN ($where)";
	}
	
	/**
	 * Is find value in cache
	 * 
	 * @return bool
	 */
	public function isCached()
	{
		if (count($this->_value) == 0) {
			return false;
		} else { 
			return true;
		}
	}
	
	/**
	 * Find and set search value from cache
	 * 
	 * @return void
	 */
	protected function _setValue()
	{
		if ($this->_criteria === self::CRITERIA_EQ) {
			$this->_value = array_search($this->_value, $this->_cache);
		} elseif ($this->_criteria === self::CRITERIA_LIKE) {
			$this->_value = $this->_arraySearch($this->_value, $this->_cache);
		} elseif ($this->_criteria === self::CRITERIA_BEGINS) {
			$this->_value = $this->_arraySearch($this->_value, $this->_cache, true);
		}
	}
	
	/**
	 * Search value in array
	 * 
	 * @param string $needle
	 * @param array $haystack
	 * @param bool $type
     * @return array
	 */
	protected function _arraySearch($needle, array $haystack, $type = NULL)
	{
		$keys = [];
		$needle = strtolower($needle);
		foreach ($haystack as $key => $value) {
			$value = strtolower($value);
			if (strlen($needle) > strlen($value)) {
				continue;
			} elseif (strlen($needle) == strlen($value)) {
				if ($needle == $value) {
					$keys[] = $key;
				}
			} elseif ($type === true) {
				$value = substr($value,0,strlen($needle));
				if ($needle == $value) {
					$keys[] = $key;
				}
			} elseif ($type === false) {
				$value = substr($value,-strlen($needle));
				if ($needle == $value) {
					$keys[] = $key;
				}
			} elseif (strpos($value, $needle) !== false) {
				$keys[] = $key; 
			}
		}
		
		return $keys;
	}
}
