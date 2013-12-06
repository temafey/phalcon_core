<?php
/**
 * @namespace
 */
namespace Engine\Crud\Grid\Column;

use Engine\Crud\Grid\AbstractGrid,
    Engine\Crud\Container\Grid as GridContainer,
	Phalcon\Filter;
	
/**
 * Interface of grid column
 *
 * @category   Engine
 * @package    Crud
 * @subpackage Grid
 */
interface Column
{
	/**
	 * Render column
	 * 
	 * @param mixed $row
	 * @return string
	 */
	public function render($row);
	
	/**
	 * Update grid container
	 * 
	 * @param \Engine\Crud\Container\Grid\Adapter $container
	 * @return \Engine\Crud\Grid\Column\Column
	 */
	public function updateContainer(GridContainer\Adapter $container);
	
	/**
	 * Update container data source
	 * 
	 * @param mixed $dataSource
	 * @return \Engine\Crud\Grid\Column\Column
	 */
	public function updateDataSource($dataSource);
}