<?php
/**
 * @namespace
 */
namespace Engine\Crud\Grid\Column;

use Engine\Crud\Grid\AbstractGrid as Grid;
	
/**
 * Standart column
 *
 * @uses       \Engine\Crud\Grid\Exception
 * @uses       \Engine\Crud\Grid\Filter
 * @uses       \Engine\Crud\Grid
 * @category   Engine
 * @package    Crud
 * @subpackage Grid
 */
class Date extends Base
{
    /**
     * Sort direction
     * @var string
     */
    protected $_sortDirection = Grid::DIRECTION_DESC;

    /**
     * DAte format
     * @var string
     */
    protected $_format = "Y-m-d H:i:s";

    /**
     * Return render value
     * (non-PHPdoc)
     * @see \Engine\Crud\Grid\Column\AbstractColumn::render()
     * @param mixed $row
     * @return string
     */
	public function render($row)
	{
		$value = $row[$this->_key];
		$value = $this->filter($value);
		$timestamp = strtotime($value);
		
		return date($this->_format, $timestamp);
	}
}