<?php
/**
 * @namespace
 */
namespace Engine\Crud\Form;

use Engine\Crud\Form;

/**
 * Form field
 *
 * @category   Engine
 * @package    Crud
 * @subpackage Form
 */
abstract class Field implements FieldInterface
{
    use \Engine\Crud\Tools\Filters,
        \Engine\Crud\Tools\Validators,
        \Engine\Crud\Tools\FormElements,
        \Engine\Crud\Tools\Renderer,
        \Engine\Crud\Tools\Attributes;
	/**
	 * Form
	 * @var \Engine\Crud\Form
	 */
	protected $_form;

	/**
	 * Form item id
	 * @var integer
	 */
	protected $_id = null;
	
	/**
     * Required flag
     * @var bool
     */
	protected $_required = false;
	
	/**
     * Hidden flag
     * @var bool
     */
	protected $_hidden = false;
	
	/**
     * Not edit flag
     * @var bool
     */
	protected $_notEdit = false;
	
	/**
	 * is save field value
	 * @var bool
	 */
	protected $_notSave = false;

	/**
	 * Value separator
	 * @var string
	 */
	protected $_separator = null;
	
	/**
	 * Field constructor
	 *
     * @param string $label
	 * @param string $name
	 * @param string $desc
	 * @param string $criteria
	 * @param int $width
	 */
	public function __construct(
        $label = null,
        $name = false,
        $desc = null,
        $required = false,
        $width = 280,
        $default = null
    ) {
		$this->_label = $label;
        $this->_name = $name;
		$this->_desc = $desc;
		$this->_required = (bool) $required;
		$this->_width = intval($width);
		$this->_default = $default;
	}
	
	/**
	 * Set form object and init field key.
	 * 
	 * @param \Engine\Crud\Form $form
	 * @param string $key
	 * @return \Engine\Crud\Form\Field
	 */
	final public function init(Form $form, $key) 
	{
		$this->_form = $form;
		$this->_key = $key;
		if ($this->_name === null) {
		    $this->_name = $key;
		}
        $this->_init();
        $this->_initFilters();
        $this->_initHelpers();
		
		return $this;
	}
	
	/**
     * Initialize field (used by extending classes)
     *
     * @return void
     */
	protected function _init()
	{
        $this->_filters[] = new \Engine\Filter\Replace(chr(226).chr(128).chr(139), '');
        $this->_filters[] = [
            'filter' => 'trim',
            'options' => []
        ];

        if ($this->isRequire()) {
            $this->_validators[] = [
                'validator' => 'PresenceOf',
			    'options' => []
            ];
        }
	}

    /**
     * Initialize field helpers
     *
     * @return void
     */
    protected function _initHelpers()
    {
        $this->setHelpers([
            'standart',
            'standart\Message',
            'standart\Label',
            'standart\Element',
            'standart\Description'
        ]);
    }

    /**
     * Do something before render
     *
     * @return string
     */
    protected function _beforeRender()
    {
        $this->_element->setAttributes($this->getAttribs());
    }
	
	/**
	 * Filtering values
	 * 
	 * @param array $values
	 */
	public function fixValues(array $values) 
	{
		foreach ($values as $key => &$value) {
			$value = trim($value);
			if (empty($value)) {
				unset($values[$key]);
			}
		}
		
		return $values;
	}
	
	/**
	 * Return rendered value
	 * 
	 * @return mixed
	 */
	public function getRenderValue()
	{
		$value = $this->getValue();
		if(null !== $this->_separator && is_array($value)) {
			$value = implode($this->_separator, $value);
		}
		
		return $value;
	}
	
	/**
	 * Not save field 
	 * 
	 * @return \Engine\Crud\Form\Field
	 */
	public function notSave() 
	{
		$this->_notSave = true;
		return $this;
	}
	
    /**
     * Set required flag
     *
     * @param  bool $flag Default value is true
     * @return \Engine\Crud\Form\Field
     */
    public function setRequired($flag = true)
    {
        $this->_required = (bool) $flag;
        return $this;
    }
	
	/**
	 * Is field required
	 * 
	 * @return bool
	 */
	public function isRequire() 
	{
		return $this->_required;
	}
	
    /**
     * Set hidden flag
     *
     * @param  bool $flag Default value is true
     * @return \Engine\Crud\Form\Field
     */
    public function setHidden($flag = true)
    {
        $this->_hidden = (bool) $flag;
        return $this;
    }
	
	/**
	 * Is field hidden
	 * 
	 * @return bool
	 */
	public function isHidden() 
	{
		return $this->_hidden;
	}
	
    /**
     * Set not edit flag
     *
     * @param  bool $flag Default value is true
     * @return \Engine\Crud\Form\Field
     */
    public function setNotEdit($flag = true)
    {
        $this->_notEdit = (bool) $flag;
        return $this;
    }
	
	/**
	 * Is field not edit
	 * 
	 * @return bool
	 */
	public function isNotEdit() 
	{
		return $this->_notEdit;
	}
	
	/**
	 * Update field
     *
	 * return void
	 */
	public function updateField()
	{		
	}
	
	/**
	 * Return field save data
	 * 
	 * @return array|bool
	 */
	public function getSaveData() 
	{
		if ($this->_notSave) {
		    return false;
		}
		$data = [];
		$data['model'] = 'default';
		$data['data'] = ['key' => $this->getName(), 'value' => $this->getValue()];

		return $data;
	}

	/**
	 * Before save field trigger
	 * 
	 * @param array $data
	 */
	public function preSaveAction(array $data) {}

	/**
	 * After save field trigger
	 * 
	 * @param array $data
	 */
	public function postSaveAction(array $data) {}

	/**
	 * Set separator for explode field value
	 *  
	 * @param string $separator
	 * @return \Engine\Crud\Form\Field
	 */
	public function setSeparator($separator)
	{
		$this->_separator = $separator;
		return $this;
	}
	
	/**
	 * Return separator
	 * 
	 * @return string
	 */
	public function getSeparator()
	{
	    return $this->_separator;
	}
	
	/**
	 * Set id value when in parent form object execute loadData function
	 * 
	 * @return \Engine\Crud\Form\Field
	 */
	public function setId($id)
	{
		$this->_id = $id;
		return $this;
	}

    /**
     * Return form row primary value
     *
     * @return int|string
     */
    public function getId()
    {
        if ($this->_id) {
            return $this->_id;
        }
        return null;
    }
	
	/**
	 * Return form
	 * 
	 * @return \Engine\Crud\Form
	 */
	public function getForm()
	{
		return $this->_form;
	}
}