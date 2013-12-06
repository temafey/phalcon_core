<?php
/**
 * @namespace
 */
namespace Engine\Crud\Container\Grid;

/**
 * Grid Container Adapter interface.
 *
 * @category   Engine
 * @package    Crud
 * @subpackage Container
 */
interface Adapter
{	
	/**
	 * Return data array
	 * 
	 * @param mixed $dataSource
	 * @return array
	 */
	public function getData($dataSource);
	
	/**
	 * Return data source obejct
	 * 
	 * @return \Engine\Mvc\Model\Query\Builder $dataSource
	 */
	public function getDataSource();
	
	/**
	 * Return data source filter object by params
	 *
     * @return \Engine\Filter\SearchFilterInterface
	 */
	public function getFilter();
	
	/**
	 * Set column to container
	 * 
	 * @param string $key
	 * @param string $name
	 * @return \Engine\Crud\Container\Grid\Adapter
	 */
	public function setColumn($key, $name);
	
	/** 
	 * Update rows by primary id values
	 * 
	 * @param array $id
	 * @param array $data
	 * @return bool|array
	 */
	public function update(array $ids, array $data);
	
	/**
	 * Delete rows by primary value
	 * 
	 * @param array $ids
	 * @return bool
	 */
	public function delete(array $ids);
}