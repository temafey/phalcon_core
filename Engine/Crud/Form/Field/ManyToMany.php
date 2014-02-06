<?php
/**
 * @namespace
 */
namespace Engine\Crud\Form\Field;

use Engine\Crud\Form\Field;

/**
 * ManyToMany field
 *
 * @category   Engine
 * @package    Crud
 * @subpackage Form
 */
class Many2Many extends Field
{
	/**
	 * Element type
	 * @var string
	 */
	protected $_type = 'multiCheckbox';
	
	/**
	 * Options row name
	 * @var string
	 */
	protected $_optionName;
	
	/**
	 * Parent model
	 * @var \Engine\Mvc\Model
	 */
	protected $_model;
	
	/**
	 * Many2many table for saving joins with categories. 
	 * @var \Engine\Mvc\Model
	 */
	protected $_workingModel;
	
	/**
	 * Ref field name
	 * @var string
	 */
	protected $_key;

    /**
     * Ref parent model field name
     *
     * @var string
     */
    protected $_keyParent;

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
	public $fields = null;
	
	/**
	 * Order of many2many model
	 * @var string
	 */
	public $workingModelOrder;
	
	/**
	 * Options select where condition
	 * @var string|array
	 */
	public $where;
	
	/**
	 * Category order
	 * @var string
	 */
	public $categoryOrder = null;

	/**
	 * Is table reference
	 * @var boolean
	 */
	protected $_noTableReference = false;
	
	/**
	 * S
	 * Enter description here ...
	 * @var array
	 */
	protected $_defaultSelectedOptions = [];
	
	/**
	 * Saving parent ids for checked values.
	 * @var bool
	 */
	protected $_saveAllParents;
	
	/**
	 * Name of field in category table for find patents.
	 * @var string
	 */
	protected $_modelParentField;
	
	/**
	 * Flag set true when many2many relationship was updated.
	 * @var bool
	 */
	private $_updateFlag = false;
	
	/**
	 * Array of additional columns
	 * @var array
	 */
	private $_additionalColumns = [];
	
	/**
	 * Is fetch data from database for filter select form options.
	 * @var bool
	 */
	protected $_loadOptions;
		
	/**
	 * Onchange attribute action 
	 * @var string
	 */
	protected $_onChangeAction = false;
	
	/**
     * @param string $label
	 * @param string $model
	 * @param string $workingModel
	 * @param string $optionName
	 * @param string $name
	 * @param string $description
	 * @param bool $required
	 * @param int $width
	 * @param array $default
	 * @param bool $saveAllParents
	 * @param string $modelParentField
	 * @param array $additionalColumns
	 * @param bool $loadOptions
	 */
	public function __construct(
        $label = null,
        $model,
        $workingModel,
        $optionName = null,
        $name = null,
        $description = null,
        $required = false,
        $width = 280,
        $default = null,
        $saveAllParents = false ,
        $modelParentField = null,
        $additionalColumns = [],
        $loadOptions = true
    ) {
		$this->_model = new $model;
	    $this->_workingModel = new $workingModel;
	    $this->_optionName = $optionName;
	    
		$this->_saveAllParents = $saveAllParents;
		$this->_modelParentField = $modelParentField;
	    $this->_additionalColumns = $additionalColumns;
	    $this->_loadOptions = $loadOptions;

		parent::__construct($label, $name, $description, $required, $width, $default);
	}

    /**
     * Initialize field (used by extending classes)
     *
     * @return void
     */
	protected function _init()
	{
		$this->_helper = 'MyFormMultiCheckbox';
		
		if (isset($this->_key)) {
			throw new \Engine\Exception("Outdated Many2Many settings for ".get_class($this->_form)." - ".get_class($this->_model));
		}
		if (!$this->_noTableReference) {
			$refModel = $this->_workingModel->getRelationPath($this->_model);
			$refFormModel = $this->_workingModel->getRelationPath($this->_form->getContainer()->getModel());

			$this->_key = $refModel->getFields();
			$this->_keyParent = $refFormModel->getFields();
		}
	}

    /**
     * Update field
     *
     * return void
     */
	public function updateField()
	{
		$selected_options = [];
		if (null !== $this->_id && !$this->_noTableReference) {
			$params = [$this->_keyParent." = :id:"];
            $params['bind'] = ['id' => $this->_id];
			if ($this->workingModelOrder) {
				$params['order'] = $this->workingModelOrder;
			}
			$selected_data = $this->_workingModel->find($params)->toArray();
			$selected_options = $this->selectedArray($selected_data);
		}

		$options_function = (null !== $this->fields) ? 'prepareOptionsAll' : 'prepareOptions';
		if ($this->_loadOptions) {
			$builder = $this->_model->queryBuilder();
			$options = \Engine\Crud\Tools\Multiselect::$options_function($builder, $this->_optionName, $this->category, $this->categoryName, $this->where, $this->emptyCategory, $this->emptyItem, true, $this->fields, $this->categoryOrder);
		} else {
			$options = [];
		}

		$selected_options = array_merge($selected_options, $this->_default);

		$this->setAttrib('class', 'multiCheckboxContent');
		$this->_element->setOptions($options);
		
		$this->_value = $this->getValue();
		$this->_element->setDefault($selected_options);
		
		if($this->_onChangeAction) {
			$this->setAttrib('onchange', $this->_onChangeAction);
		}
	}

	/**
	 * Return linear array
	 * 
	 * @param array $selected_data
	 * @return array
	 */
	protected function selectedArray($selected_data) 
	{
		return \Engine\Tools\Arrays::assocToLinearArray($selected_data, $this->_key);
	}

	/**
	 * Return field save data
	 * 
	 * @return array|bool
	 */
	public function getSaveData() 
	{
        return false;
	}
	
	/**
	 * Save data in many2many field. 
	 * 
	 * @param array $data
	 * @return void
	 */
	public function postSaveAction($data = null) 
	{
		if (!$this->_notSave) {
	        return false;
	    }
	    if (null === $this->_id) {
		    return false;
		}
		
		$data = [];
		$formValues = $this->getValue();
		if (is_string($formValues)) {
			$formValues = [$formValues];
		}
		
	    if ($this->_saveAllParents && count($formValues) > 0) {
	        $parentIds = $this->_findAllParents($formValues);
	        $formValues = array_unique(array_merge($formValues, $parentIds));
		}
		$savedData = $this->getSavedData();
		$savedValues = $this->getOptionIds($savedData);		
		
		$delete = array_diff($savedValues, $formValues);
		$insert = array_diff($formValues, $savedValues); 
        $update = array_intersect($formValues, $savedValues);
		
		$data['insert'] = [];
		$data['delete'] = [];
		$data['update'] = [];
		
		$additionalData = [];
	    if (!empty($this->_additionalColumns) && is_array($this->_additionalColumns)) {
            $model = $this->_form->getContainer()->getModel();
	        $rowData = $model->findFirst($this->_id);
		    $rowData = $rowData->toArray();
	        foreach ($this->_additionalColumns as $refColumn => $depColumn) {
	            $additionalData[$depColumn] = $rowData[$refColumn];
	        }
	    }

		foreach ($insert as &$v) {
		    $row = array_merge(array($this->_keyParent => $this->_id, $this->_key => $v), $additionalData);		    
			$data['insert'][] = $row;
		}
		foreach ($delete as $v) {
			$data['delete'][] = $this->_workingModel->q($this->_keyParent." = ?", $this->_id) . " AND " . $this->_workingModel->q($this->_key." = ?", $v);
		}		
		if (!empty($this->_additionalColumns) && is_array($this->_additionalColumns)) {
    		foreach ($update as $v) {
    		    $row = array_merge(array($this->_keyParent => $this->_id, $this->_key => $v), $additionalData);
    		    $where = $this->_workingModel->q($this->_keyParent." = ?", $this->_id) . " AND " . $this->_workingModel->q($this->_key." = ?", $v);
    			$data['update'][] = array('where' => $where, 'data' =>  $row);
    		}
		}
		
		$db = $this->_model->getWriteConnection();
		$db->begin();
		
		if(count($data ['delete']) > 0 || count($data ['insert']) > 0) {
		    $this->_updateFlag = true;
		}
		
		try {
			foreach ($data['delete'] as &$delete) {
				$this->_workingModel->delete($delete);
			}
			foreach ($data['insert'] as &$insert) {
				$this->_workingModel->insert($insert);
			}
		    foreach ($data['update'] as &$update) {
				$this->_workingModel->update($update['data'], $update['where']);
			}
			$db->commit();

		} catch (\Engine\Exception $e) {
			$db->rollback ();
			echo $e->getMessage ();
			die ();
		}

		return true;
	}
	
	/**
	 * Find all category parent ids for all save values
	 * 
	 * @param array $ids
	 * @return array 
	 */
	protected function _findAllParents(array $ids) 
	{
	    $parents = [];
        $primary = $this->_model->getPrimary();
	    foreach ($ids as &$id) {
    	    $row = $this->_model->findFirst($id);
    	    if (null === $row) {
    	        continue;
    	    }
    	    $row = $row->toArray();    	    
            $parent = (isset($row[$this->_modelParentField])) ? $row[$this->_modelParentField] : 0;

            while($parent != 0){
        	    $row = $this->_model->findFirst($parent);
        	    $row = $row->toArray();
                $parent = $row[$this->_modelParentField];
                if(!in_array($row[$primary], $parents)) {
                	$parents[] = $row[$primary];
                }
            }
	    }
	    
	    return $parents;
	}
	
	/**
	 * Fetch and return all saved options.
	 * 
	 * @return array
	 */
	public function getSavedData($id = null)
	{
		if ($id === null) {
			$id = $this->_id;
		}
		if ($id === null) {
			throw new \Engine\Exception("Not set id for fetch saved data!");
		}
		
		return $this->_workingModel->find([$this->_keyParent." = :id:", 'bind' => ['id' => $id]])->toArray();
	}
	
	/**
	 * Generate and return array of saved options rows. 
	 * 
	 * @param array $data
	 * @return array
	 */
	public function getOptionIds(array $data)
	{
		$ids = array();		
		foreach ($data as &$value) {
		    $ids[] = $value[$this->_key];
		}
				
		return $ids;
	}
	
	/**
	 * Return was many2many update or not.
	 * 
	 * @return bool
	 */
	public function isUpdated()
	{
	    return $this->_updateFlag;
	}
	
	/**
	 * Set param _loadOptions to false 
	 * 
	 * @return \Engine\Crud\Form\Field\Many2Many
	 */
	public function notLoadOptions()
	{
		$this->_loadOptions = false;
        return $this;
	}
	
	/**
	 * Set onchange action
	 * 
	 * @param string $onchange
	 * @return @return \Engine\Crud\Form\Field\Many2Many
	 */
	public function setOnchangeAction($onchange)
	{
		$this->_onChangeAction = $onchange;
		return $this;
	}
}
