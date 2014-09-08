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
class Date extends Standart
{
    /**
     * Element type
     * @var string
     */
    protected $_type = 'date';

    /**
     * Initialize field (used by extending classes)
     *
     * @return void
     */
    protected function _init()
    {
        parent::_init();

        $this->_filters[] = [
            'filter' => 'trim',
            'options' => []
        ];

        $this->_validators[] = [
            'validator' => 'StringLength',
            'options' => [
                'max' => $this->_length
            ]
        ];
    }

    /**
     * Return datasource filters
     *
     * @param \Engine\Crud\Container\AbstractContainer $container
     * @return \Engine\Filter\SearchFilterInterface
     */
    public function getFilter(Container $container)
    {
		$value = $this->getValue();
        if (!$value) {
            return false;
        }
        $value = str_replace("-", "/", $value);
        $value = date('Y-m-d H:i:s', strtotime($value));

        return $container->getFilter('standart', $this->_name, $value, $this->_criteria);
	}
}
