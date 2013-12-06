<?php
/**
 * @namespace
 */
namespace Engine\Crud\Grid\Filter\Field;

use Engine\Filter\SearchFilterInterface as Criteria,
    Engine\Crud\Container\AbstractContainer as Container;

/**
 * Grid filter field
 *
 * @category   Engine
 * @package    Crud
 * @subpackage Grid
 */
class Between extends Standart
{
    /**
     * Return datasource filters
     *
     * @param \Engine\Crud\Container\AbstractContainer $container
     * @return \Engine\Filter\SearchFilterInterface
     */
    public function getFilter(Container $container)
    {
		$values = $this->getValue();
		if ($values) {
			$values = $this->_parseValue($values);
            return $container->getFilter('between', $values['min'], $values['max'], $this->criteria);
		}
		
		return false;
	}

    /**
     * Parse value string
     *
     * @param $values
     * @return array|bool
     */
    protected function _parseValue($values)
	{
		if (is_string($values) && strpos($values, ';') !== false) {
			$values = explode(';', $values);		
		} 
		if (is_array($values)) {
			$min = (isset($values['min'])) ? $values['min'] : $values[0];
			$max = (isset($values['max'])) ? $values['max'] : $values[1];
			
			return ['min' => $min, 'max' => $max];
		}

		return false;
	}
	
}
