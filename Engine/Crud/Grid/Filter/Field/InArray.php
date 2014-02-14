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
class InArray extends Standart
{
    /**
     * Values delimiter
     * @var string
     */
    protected $_delimiter;

    /**
     * Constructor
     *
     * @param string $label
     * @param string $name
     * @param string $criteria
     * @param string $delimiter
     */
    public function __construct(
        $label = null,
        $name = null,
        $criteria = Criteria::CRITERIA_IN,
        $delimiter = ","
    ) {
        $this->_delimiter = $delimiter;
        parent::__construct($name, $label, $criteria);
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
        if ($values === null || $values === false ||(is_string($values) && trim($values) == "")) {
            return false;
        }

        if (!is_array($values)) {
            $values = explode($this->_delimiter, $values);
        }

        return $container->getFilter('in', $this->criteria, $values);
		
    }
}
