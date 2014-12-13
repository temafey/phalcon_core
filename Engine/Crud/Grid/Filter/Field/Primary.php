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
class Primary extends Standart
{
    /**
     * Constructor
     *
     * @param string $title
     * @param string $desc
     * @param string $criteria
     * @param int $width
     */
    public function __construct(
        $label = null,
        $desc = null,
        $criteria = Criteria::CRITERIA_EQ,
        $width = 280,
        $defaultValue = false,
        $length = 100)
    {
        $this->_label = $label;
        $this->_desc = $desc;
        $this->_criteria = $criteria;
        $this->_width = intval($width);
        $this->_default = $defaultValue;
        $this->_length = intval($length);
    }

	/**
     * Initialize field (used by extending classes)
     *
     * @return void
     */
	protected function _init()
	{
        $this->_filters[] = [
            'filter' => 'trim',
            'options' => []
        ];
	}

    /**
     * Apply field filter value to dataSource
     *
     * @param mixed $dataSource
     * @param \Engine\Crud\Container\AbstractContainer $container
     * @return \Engine\Crud\Grid\Filter\Field
     */
    public function applyFilter($dataSource, Container $container)
    {
        $model = $dataSource->getModel();
        $this->_name = $model->getPrimary();

        return parent::applyFilter($dataSource, $container);
    }

}
