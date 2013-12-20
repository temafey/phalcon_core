<?php
/**
 * @namespace
 */
namespace Engine\Crud\Grid\Filter\Field;

use Engine\Crud\Grid\Filter\Field,
    Engine\Filter\SearchFilterInterface as Criteria,
    Engine\Crud\Container\AbstractContainer as Container;

/**
 * Grid filter field
 *
 * @category   Engine
 * @package    Crud
 * @subpackage Grid
 */
class Standart extends Field
{
    /**
     * Element type
     * @var string
     */
    protected $_type = 'text';

	/**
	 * Max string length
	 * @var integer
	 */
	protected $_length;
	
	/**
     * Constructor
	 *
     * @param string $title
	 * @param string $name
	 * @param string $desc
	 * @param string $criteria
	 * @param integer $width
	 */
	public function __construct(
        $label = null,
        $name = false,
        $desc = null,
        $criteria = Criteria::CRITERIA_EQ,
        $width = 60,
        $default = null,
        $length = 100)
	{
        parent::__construct($label, $name, $desc, $criteria);
        $this->_width = intval($width);
        if ($default !== null) {
            $this->_default = $default;
        }
		$this->_length = intval($length);
	}

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
		$values = $this->getValue();
		if ($values === null || $values === false || (is_string($values) && $values == "")) {
		    return false;
		}
		$filters = [];
		if (!is_array($values)) {
			$values = [$values];
		}

		foreach ($values as $val) {
			if(trim($val) == "" || array_search($val, $this->_exceptionsValues)) {
				continue;
			}
			$filters[] = $container->getFilter('search', [$this->_name => $this->_criteria], $val);
		}
		$filter = $container->getFilter('compound', 'OR', $filters);
		
		return $filter;
	}
}
