<?php
/**
 * @namespace
 */
namespace Engine\Crud\Grid\Column;

use Engine\Crud\Grid\Column,
    Engine\Crud\Grid,
    Engine\Crud\Container\Grid as GridContainer,
	Phalcon\Filter;
	
/**
 * Class Base
 *
 * @category   Engine
 * @package    Crud
 * @subpackage Grid
 */
class Base extends Column
{
    /**
     * Update grid container
     *
     * @param \Engine\Crud\Container\Grid\Adapter $container
     * @return \Engine\Crud\Grid\Column
     */
    public function updateContainer(\Engine\Crud\Container\Grid\Adapter $container)
    {
        $container->setColumn($this->_key, $this->_name);
        return $this;
    }

	/**
	 * Return render value
	 * (non-PHPdoc)
	 * @see \Engine\Crud\Grid\Column::render()
	 * @param mixed $row
	 * @return string
	 */
	public function render($row)
	{
		$value = $row[$this->_key];
		$value = $this->filter($value);
		
		return $value;
	}
	
}