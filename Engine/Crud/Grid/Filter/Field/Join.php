<?php
/**
 * @namespace
 */
namespace Engine\Crud\Grid\Filter\Field;

use Engine\Filter\SearchFilterInterface as Criteria,
    Engine\Db\Filter\Compound,
    Engine\Crud\Container\AbstractContainer as Container;

/**
 * Grid filter field
 *
 * @category   Engine
 * @package    Crud
 * @subpackage Grid
 */
class Join extends ArrayToSelect
{
	/**
	 * Form element type
	 * @var string
	 */
	protected $_type = 'select';

	/**
	 * Parent model
	 * @var \Engine\Mvc\Model
	 */
	protected $_model;

	/**
	 * Filter join path
	 * @var string|array
	 */
	protected $_path;
	
	/**
	 * Options row name
	 * @var string
	 */
	protected $_optionName = 'name';

	/**
	 * Options category model
	 * @var \Engine\Mvc\Model
	 */
	public $category;
	
	/**
	 * Optiosn category row name
	 * @var string
	 */
	public $categoryName = 'name';
	
	/**
	 * Empty category value
	 * @var string
	 */
	public $emptyCategory;
	
	/**
	 * Empty item value
	 * @var string
	 */
	public $emptyItem;
	
	/**
	 * Addition select fields
	 * @var array
	 */
	public $fields = [];
	
	/**
	 * Options select where condition
	 * @var string|array
	 */
	public $where;

	/**
	 * @var string
	 */
	protected $_glue = Compound::GLUE_OR;
	
	/**
	 * Separate filter for simple queries.
	 * @var bool
	 */
	protected $_separatedQueries;
	
	/**
	 * Add default value to filters
	 * @var bool
	 */
	protected $_enableDefaultValue = false;
	
	/**
	 * Constructor
	 *
     * @param string $title
     * @param string|\Engine\Mvc\Model $model
	 * @param string|array $path
	 * @param string $optionName
	 * @param string $desc
	 * @param string $criteria
	 * @param bool $loadSelectOptions
	 * @param bool $separatedQueries
	 */
	public function __construct(
        $label = null,
        $model,
        $name = false,
        $optionName = null,
        $path = null,
        $desc = null,
        $criteria = Criteria::CRITERIA_EQ,
        $width = 280,
        $loadSelectOptions = true,
        $separatedQueries = false,
        $default = null
    ) {
        $this->_label = $label;
        $this->_name = $name;
        $this->_desc = $desc;
        $this->_criteria = $criteria;
        $this->_width = intval($width);

		$this->_model = $model;
		$this->_optionName = $optionName;
		$this->_path = $path;
		$this->_loadSelectOptions = $loadSelectOptions;
		$this->_separatedQueries = $separatedQueries;
	}

    /**
     * Update field
     *
     * return void
     */
    public function updateField()
	{		
		parent::updateField();
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
        if (!$this->_name) {
            $model = $dataSource->getModel();
            $path = ($this->_path) ? $this->_path : $this->_model;
            $relations = $model->getRelationPath($path);
            $relation = array_pop($relations);
            $this->_name = $relation->getFields();
        }

		if ($filters = $this->getFilter($container)) {
			if ($this->_separatedQueries === false) {
                $dataSource->columnsJoinOne($this->_path);
                $filterPath = $container->getFilter('path', $this->_path, $filters);
                $filterPath->applyFilter($dataSource);
			} else {
				$filters = $this->_getSeparateFilters($filters, $dataSource->getModel(), $container);
                $filters->applyFilter($dataSource);
			}
		}

        return $this;
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
		$filters = [];
		if (!is_array($values)) {
			$values = [$values];
		}

		foreach ($values as $val) {
			if (trim($val) == "" || $val == -1 || $val === false  || array_search($val, $this->_exceptionValues, empty($val) && $val !== '0')) {
				continue;
			}
			if ($val == '{{empty}}') {
			    $val = '';
			}
			$filters[] = $container->getFilter('standart', $this->_name, $val, $this->_criteria);
		}
		
		if (!empty($filters) && $this->_enableDefaultValue) {
			$filters[] = $container->getFilter('standart', $this->_name, $this->_default, $this->_criteria);
		}

        return $container->getFilter('compound', $this->_glue, $filters);
    }

    /**
     *
     *
     * @param $filters
     * @param \Engine\Mvc\Model $model
     * @param \Engine\Crud\Container\AbstractContainer $container
     * @return \Engine\Filter\SearchFilterInterface
     */
    protected function _getSeparateFilters($filters, \Engine\Mvc\Model $model, Container $container)
	{
		$path = $this->_path;
		$rule = array_shift($path);
        $relations = $model->getRelationPath($rule);
        $relation = array_shift($relations);
        $fields = $relation->getFields();
        $refModel = new $relation->getReferencedModel();
        $refFields = $relation->getReferencedFields();
        $options = $relation->getOptions();

		$queryBuilder = $refModel->queryBuilder();
        $queryBuilder->columns($refFields);

		if (count($path) > 0) {
            $queryBuilder->columnsJoinOne($path, null);
            $filterPath = $container->getFilter('path', $path, $filters);
            $queryBuilder->filter($filterPath);
		} else {
            $queryBuilder->filter($filters);
		}
		$rows = $queryBuilder->getQuery()->execute()->toArray();
		$values = [];
		foreach ($rows as $val) {
			$values[] = $val[$prevRef['refColumns']];
		}

		return $container->getFilter('in', [$prevRef['columns'] => Criteria::CRITERIA_EQ], $values);
	}
	
   /**
   	* Return options array
   	*
   	* @return array
  	*/	
	public function getOptions()
	{
		if(empty($this->_options)) {
			$this->_setOptions();
		}
		return $this->_options;
	}
	
	/**
	 * Set options
	 * 
	 * @return void
	 */
	protected function _setOptions()
	{
		if (is_string($this->_model)) {
			$this->_model = new $this->_model;
		}
		$queryBuilder = $this->_model->queryBuilder();
			
		$this->_options = \Engine\Crud\Tools\Multiselect::prepareOptions($queryBuilder, $this->_optionName, $this->category, $this->categoryName, $this->where, $this->emptyCategory, $this->emptyItem, $this->fields);
	}
}