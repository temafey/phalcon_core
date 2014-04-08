<?php
/**
 * @namespace
 */
namespace Engine\Crud\Grid\Column;

use Engine\Crud\Grid,
    Engine\Crud\Container\Grid as GridContainer,
    Engine\Crud\Form;
	
/**
 * Image join column
 *
 * @uses       \Engine\Crud\Grid\Exception
 * @uses       \Engine\Crud\Grid\Filter
 * @uses       \Engine\Crud\Grid
 * @category   Engine
 * @package    Crud
 * @subpackage Grid
 */
class FormField extends Base
{	
	/**
	 * Form object for update table column.
	 * @var \Engine\Crud\Form
	 */
	protected $_form = null;

	/**
	 * @var
	 */
	protected $_element;

	protected $_elementView;
	protected $_elementHelper;
	protected $_elementAttribs;
	protected $_elementName;
	protected $_elementOptions;

	/**
	 * Form element attributes
	 * @var array
	 */
	protected $_attibutes;

	/**
	 * Constructor
	 * 
	 * @param string $title
	 * @param string $name
	 * @param bool $isSortable
	 * @param array $attibutes
	 * @param int $width
	 */
	public function __construct($title, $name = null, $isSortable = true, array $attibutes = [], $width = 200)
	{
		parent::__construct($title, $name, $isSortable, false, $width);
		$this->_attibutes = $attibutes;
	}

	public function init() 
	{
		$field = (!empty($this->_name)) ? $this->_form->getFieldByName($this->_name) : $this->_form->getFieldByKey($this->_key);
		if(!$field) {
			throw new \Engine\Exception('Field like '.$this->name.' does not exists in '.get_class($this->_form).' form!');
		}
		
		$field->setForm($this->_form);
		$this->_element = $field->createElement();
		if (is_array($this->_element)) {
		    $element = each($this->_element);
		    $element = $element['value'];
            $this->_elementView = $element->getView();
            $this->_elementHelper = $element->helper;
	    	$this->_elementAttribs = $element->getAttribs();
    		$this->_elementName = $element->getFullyQualifiedName ();
		    $this->_elementOptions = $element->options;
		} else {
		    $this->_elementView = $this->_element->getView ();
		    $this->_elementHelper = $this->_element->helper;
		    $this->_elementAttribs = $this->_element->getAttribs ();
		    $this->_elementName = $this->_element->getFullyQualifiedName ();
		    $this->_elementOptions = $this->_element->options;
		}

		if ($this->_element instanceof \Engine\Forms\Element\Checkbox) {
			$this->_elementOptions = array ('checked' => 1, 'unChecked' => 0 );
		}
	}

	/**
	 * (non-PHPdoc)
	 * @see Crud\Grid\Column.Standart::render()
	 */
	public function render($row) 
	{
		$attribs ['rowId'] = $this->row_id;
		$attribs ['id'] = $this->key."-".$this->row_id;

		foreach ( $this->attibutes as $key => $value ) {
			$attribs [$key] = $value;
		}

		if ($this->_element instanceof \Zend\Form\Element\Checkbox) {
			$attribs ['checked'] = $row [$this->key];
		}
		$fieldName = (!empty($this->name)) ? $this->_form->getField\NameByColumn\Name ( $this->name ) : $this->_form->getField\NameByField\Name ( $this->key );
		$value = $this->_form->fields [$fieldName]->setValue ( $row [$this->key] )->getValue ();
		if(is_array($value)) {
			$value = $row [$this->key];
		}
		$helper = $this->_elementHelper;
		$html = ($this->_elementView instanceof Zend_View_Interface) ? $this->_elementView->$helper ( $this->_elementName, $value, $attribs, $this->_elementOptions ) : $value;

		return "<span id=\"$this->row_id\">$html</span>";
	}

	/**
	 * Update column in database by primary key.
	 * 
	 * @param int $id
	 * @param string $value
	 */
	public function updateField($id, $value)
	{
		if ($this->_element->isValid($value)) {
			$model = $this->_form->getModel();
			$field = $this->_form->getFieldByName($this->_name);
			$field->setValue($value);
			$value = $field->getValue();
			$column = $field->getName();
			$data = array($column => $value);
			$where = $model->q($model->getPrimary()." = ?", $id);

			return $model->update($data, $where);
		}
		
		return array('valid' => false, 'errors' => $this->_element->getErrors());
	}
	
	/**
	 * Set \Engine\Crud\Form class name.
	 * 
	 * @param string|\Crud\Form\Form $form
	 * @return \Engine\Crud\Grid\Column\FormColumn
	 */
	public function setForm($form)
	{
		if(!($form instanceof Form)) {
			$form = \Tools\Registry::singleton($form);
		}
	    $this->_form = $form;
	    
	    return $this;
	}
}