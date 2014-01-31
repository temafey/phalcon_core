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
class Search extends Standart
{
	/**
	 * Search filter fields
	 * @var array
	 */
	protected $_fields = [];
	
	/**
	 * Constuctor
	 *
     * @param string $label
	 * @param string $name
	 * @param array $fields
	 * @param string $desc
	 * @param int $width
	 * @param string $default
	 * @param int $length
	 */
	public function __construct(
        $label = null,
        $name = false,
        array $fields,
        $desc = null,
        $width = 60,
        $default = null,
        $length = 255)
	{
		parent::__construct($label, $name, $desc, Criteria::CRITERIA_EQ, $width, $default, $length);
        $this->_fields = $fields;
	}

    /**
     * Return datasource filters
     *
     * @param \Engine\Crud\Container\AbstractContainer $container
     * @return \Engine\Filter\SearchFilterInterface
     */
	public function getFilter(Container $container)
	{
		$values = $this->getValue();
		if ($values === null || $values === false || (is_string($values) && trim($values) == "")) {
		    return false;
		}
		if (!is_array($values)) {
			$values = $this->_parseValue($values);
		}
		$filters = [];
		foreach ($values as $sub_values){
			$sub_filters = [];
			foreach ($sub_values as $value){
				if (empty($value)) {
					continue;
				}
				$value = trim($value);
				$tmp_filters = [];
				foreach($this->_fields as $field => $filterSetting) {
                    if (!is_array($filterSetting) && !is_array($filterSetting)) {
                        $filterSetting = ((int) $field !== $field) ? [$field => $filterSetting] : [$filterSetting => Criteria::CRITERIA_LIKE];
                    }
					if (isset($filterSetting['path'])) {
						$tmp_filters[] = $container->getFilter('path', $filterSetting['path'], $container->getFilter('search', $filterSetting['filters'], $value));
					} elseif (isset($filterSetting['cache'])) {
						$cfilter = $container->getFilter('cache', $value, $filterSetting['field'], $filterSetting['cache'], $filterSetting['criteria']);							
						if (true === $cfilter->isCached()){
							$tmp_filters[] = $cfilter;
							break; 
						}
					} else {
                        $tmp_filters[] = $container->getFilter('search', $filterSetting, $value);
                    }
				}
				if (empty($tmp_filters)) {
					continue;
				}
				$sub_filters[] = $container->getFilter('compound', 'OR', $tmp_filters);
			}
			if (count($sub_filters) == 0) {
				continue;
			}
			$filters[] = $container->getFilter('compound', 'AND', $sub_filters);
		}

		if (count($filters) == 0) {
			return false;
		}

		return $container->getFilter('compound', 'OR', $filters);
	}
	
	/**
	 * Parse value string
	 * 
	 * @param string $value
	 * @return array
	 */
	protected function _parseValue($value)
	{		
		if (strpos($value, ';') !== false) {
			$values = array();
			$tmp_values = explode(';', $value);
			foreach ($tmp_values as $value){
				$values[] = $this->subParseValue($value);
			}
			return $values;			
		} else {
			return array($this->_subParseValue($value));
		}
	}
	
	/**
	 * Parse value string
	 * 
	 * @param string $value
	 * @return array
	 */
	protected function _subParseValue($value)
	{
		$values = array();
		if (strpos($value, ',') !== false) {
			return explode(',', $value);			
		} else {
			return array($value);
		}
	}
	
}
