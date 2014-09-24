<?php
/**
 * @namespace
 */
namespace Engine\Crud\Form\Field;

/**
 * ManyToOne field
 *
 * @category   Engine
 * @package    Crud
 * @subpackage Form
 */
class ManyToOne extends ArrayToSelect
{
    /**
     * Parent model
     * @var \Engine\Mvc\Model
     */
    protected $_model;

    /**
     * Options row name
     * @var string
     */
    protected $_optionName;

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
	 * Onchange attribute action 
	 * @var string
	 */
	protected $_onChangeAction = false;

    /**
     * @var bool
     */
    protected $_asynchron = true;
	
	/**
	 * Constructor
	 *
     * @param string $label
     * @param string|\Engine\Mvc\Model $model
	 * @param string $name
	 * @param string $optionName
	 * @param string $desc
	 * @param bool $required
	 * @param string $width
	 * @param string $default
	 */
	public function __construct(
        $label = null,
        $model,
        $name = false,
        $optionName =  null,
        $desc = null,
        $required = true,
        $width = 280,
        $default = null
    ) {
		parent::__construct($label, $name, [], $desc, $required, $width, $default);

        $this->_model = $model;
        $this->_optionName = $optionName;
	}

    /**
     * Initialize field (used by extending classes)
     *
     * @return void
     */
    protected function _init()
    {
        parent::_init();

        if (!$this->_name) {
            $model = $this->_form->getContainer()->getModel();
            $relations = $model->getRelationPath($this->_model);
            $relation = array_shift($relations);
            $this->_name = $relation->getFields();
        }
    }

    /**
     * Return field model class name
     *
     * @return string
     */
    public function getModel()
    {
        return (is_object($this->_model)) ? get_class($this->_model) : $this->_model;
    }

    /**
     * Return options array
     *
     * @return array
     */
    public function getOptions()
    {
        if (empty($this->_options)) {
            $this->_setOptions();
        }
        $options = $this->_options;
        $null = false;
        if ($this->_nullOption) {
            if ($this->_nullOption == -1) {
                $null = [-1 => '-'];
            } elseif (is_string($this->_nullOption)) {
                $null = ['' => $this->_nullOption];
            } elseif (is_array($this->_nullOption)) {
                $null = $this->_nullOption;
            }
            if ($null !== false) {
                $options = $null + $options;
            }
        }

        return $options;
    }

    /**
     * Set options
     *
     * @return void
     */
    protected function _setOptions()
    {
        $adapter = $this->_form->getContainer()->getModel()->getReadConnectionService();
        if (is_string($this->_model)) {
            $model = new $this->_model;
        } elseif (is_array($this->_model)) {
            $models = $this->_model;
            $model = array_pop($models);
            $model = new $model;
        }
        $model->setReadConnectionService($adapter);
        $queryBuilder = $model->queryBuilder();
        $this->_options = \Engine\Crud\Tools\Multiselect::prepareOptions($queryBuilder, $this->_optionName, $this->category, $this->categoryName, $this->where, $this->emptyCategory, $this->emptyItem, $this->fields);
    }
}
