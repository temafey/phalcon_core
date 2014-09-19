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
class ManyToMany extends Field
{
    /**
     * Element type
     * @var string
     */
    protected $_type = 'multiCheck';

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
     * many to many table for saving joins with categories.
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
    protected $_category;

    /**
     * Optiosn category row name
     * @var string
     */
    protected $_categoryName = 'name';

    /**
     * Empty category value
     * @var string
     */
    protected $_emptyCategory;

    /**
     * Empty item value
     * @var string
     */
    protected $_emptyItem;

    /**
     * Addition select fields
     * @var array
     */
    protected $_fields = null;

    /**
     * Order of many to many model
     * @var string
     */
    protected $_workingModelOrder;

    /**
     * Options select where condition
     * @var string|array
     */
    protected $_where;

    /**
     * Category order
     * @var string
     */
    protected $_categoryOrder = null;

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
     * Flag set true when many to many relationship was updated.
     * @var bool
     */
    private $_updateFlag = false;

    /**
     * Array of additional columns
     * @var array
     */
    private $_additionalColumns = [];

    /**
     * Select options array
     * @var array
     */
    protected $_options;

    /**
     * Is fetch data from database for filter select form options.
     * @var bool
     */
    protected $_loadOptions;

    /**
     * Count of all options
     * @var array
     */
    protected $_count;

    /**
     * Onchange attribute action
     * @var string
     */
    protected $_onChangeAction = false;

    /**
     * Value separator
     * @var string
     */
    protected $_separator = ', ';

    /**
     * @param string $label
     * @param string $model
     * @param string $workingModel
     * @param string $optionName
     * @param string $name
     * @param string $desc
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
        $desc = null,
        $required = false,
        $width = 450,
        $default = [],
        $saveAllParents = false ,
        $modelParentField = null,
        $additionalColumns = [],
        $loadOptions = false
    ) {
        $this->_model = $model;
        $this->_workingModel = $workingModel;
        $this->_optionName = $optionName;

        $this->_saveAllParents = $saveAllParents;
        $this->_modelParentField = $modelParentField;
        $this->_additionalColumns = $additionalColumns;
        $this->_loadOptions = $loadOptions;
        if (!is_array($default)) {
            if (!$default) {
                $default = [$default];
            } else {
                $default = [];
            }
        }
        parent::__construct($label, false, $desc, $required, $width, $default);
    }

    /**
     * Initialize field (used by extending classes)
     *
     * @return void
     */
    protected function _init()
    {
        parent::_init();

        if ($this->_name) {
            throw new \Engine\Exception("Outdated ManyToMany settings for ".get_class($this->_form)." - ".get_class($this->_model));
        }
        if (!$this->_noTableReference) {
            $workingModel = $this->_getWorkModel();
            $relationsRefModel = $workingModel->getRelationPath($this->_model);
            if (!$relationsRefModel) {
                throw new \Engine\Exception("Did not find relations between '".get_class($this->_workingModel)."' and '".get_class($this->_model)."'");
            }
            $mainModel = $this->_form->getContainer()->getModel();
            $relationsMainModel = $workingModel->getRelationPath($mainModel);
            if (!$relationsMainModel) {
                throw new \Engine\Exception("Did not find relations between '".get_class($this->_workingModel)."' and '".get_class($mainModel)."'");
            }
            $this->_name = array_shift($relationsRefModel)->getFields();
            $this->_keyParent = array_shift($relationsMainModel)->getFields();
        }
        $this->setAttrib("autoLoad", true);
    }

    /**
     * Update field
     *
     * return void
     */
    public function updateField()
    {
        $selectedOptions = [];
        if (null !== $this->_id && !$this->_noTableReference) {
            $params = [$this->_keyParent." = :id:"];
            $params['bind'] = ['id' => $this->_id];
            if ($this->_workingModelOrder) {
                $params['order'] = $this->_workingModelOrder;
            }
            $selectedData = $this->_getWorkModel()->find($params)->toArray();
            $selectedOptions = $this->selectedArray($selectedData);
        }

        if ($this->_loadOptions) {
            $this->_setOptions();
        }

        $selectedOptions = array_merge($selectedOptions, $this->_default);

        $this->_element->setOptions($this->_options);

        $this->_value = $this->getValue();
        $this->_element->setDefault($selectedOptions);

        if ($this->_onChangeAction) {
            $this->setAttrib('onchange', $this->_onChangeAction);
        }
    }

    /**
     * Return linear array
     *
     * @param array $selected
     * @return array
     */
    protected function selectedArray(array $selected)
    {
        return \Engine\Tools\Arrays::assocToLinearArray($selected, $this->_name);
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
     * Save data in many to many field.
     *
     * @param array $data
     * @return void
     */
    public function postSaveAction(array $data)
    {
        if ($this->_notSave) {
            return false;
        }
        if (null === $this->_id) {
            return false;
        }

        $data = [];
        $value = $this->getValue();
        $formValues = [];
        if (is_string($value)) {
            $value = explode($this->_separator, $value);
            if (!is_numeric($value[0])) {
                $formValues = $this->getOptionIdsByNames($value);
            } else {
                $formValues = $value;
            }
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

        foreach ($insert as $v) {
            $row = array_merge([$this->_keyParent => $this->_id, $this->_key => $v], $additionalData);
            $data['insert'][] = $row;
        }
        foreach ($delete as $v) {
            $data['delete'][] = $this->_keyParent." = ".$this->_id." AND ".$this->_name." = ".$v;
        }
        if (!empty($this->_additionalColumns) && is_array($this->_additionalColumns)) {
            foreach ($update as $v) {
                $row = array_merge([$this->_keyParent => $this->_id, $this->_name => $v], $additionalData);
                $where = $this->_keyParent." = ".$this->_id." AND ".$this->_name." = ".$v;
                $data['update'][] = ['where' => $where, 'data' =>  $row];
            }
        }

        $manager = $this->_getModel()->getDi()->get('modelsManager');
        $db = $this->_getModel()->getWriteConnection();
        $db->begin();

        if (count($data['delete']) > 0 || count($data ['insert']) > 0) {
            $this->_updateFlag = true;
        }

        try {
            $source = $this->_workingModel;
            foreach ($data['delete'] as $delete) {
                $manager->executeQuery("DELETE FROM ".$source." WHERE ".$delete);
            }
            foreach ($data['insert'] as $insert) {
                $manager->executeQuery("INSERT INTO ".$source." (".implode(",", array_keys($insert).") VALUES (".implode(",", $insert)));
            }
            foreach ($data['update'] as $update) {
                $update = [];
                foreach ($update['data'] as $key => $value) {
                    $update[] = $key." = '".$value."'";
                }
                $manager->executeQuery("UPDATE ".$source." SET ".implode(",", $update)." WHERE ".$update['where']);
            }
            $db->commit();

        } catch (\Engine\Exception $e) {
            $db->rollback();
            echo $e->getMessage();
            die();
        }
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
        $model = $this->_getModel();
        $primary = $model->getPrimary();
        foreach ($ids as $id) {
            $row = $model->findFirst($id);
            if (null === $row) {
                continue;
            }
            $row = $row->toArray();
            $parent = (isset($row[$this->_modelParentField])) ? $row[$this->_modelParentField] : 0;

            while ($parent != 0) {
                $row = $model->findFirst($parent);
                $row = $row->toArray();
                $parent = $row[$this->_modelParentField];
                if (!in_array($row[$primary], $parents)) {
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

        return $this->_getWorkModel()->find([$this->_keyParent." = :id:", 'bind' => ['id' => $id]])->toArray();
    }

    /**
     * Generate and return array of saved options rows.
     *
     * @param array $data
     * @return array
     */
    public function getOptionIds(array $data)
    {
        $ids = [];
        foreach ($data as $value) {
            $ids[] = $value[$this->_name];
        }

        return $ids;
    }

    /**
     * @param array $names
     * @return array
     */
    public function getOptionIdsByNames(array $names)
    {
        $ids = [];
        $names = \Engine\Tools\String::quote($names);
        $queryBuilder = $this->_getModel()->queryBuilder();
        $queryBuilder->columnsId();
        $queryBuilder->where("name IN (".$names.")");
        $result = $queryBuilder->getQuery()->execute();
        if ($result) {
            foreach ($result->toArray() as $row) {
                $ids[] = $row['id'];
            }
        }

        return $ids;
    }

    /**
     * Return was many to many update or not.
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
     * @return \Engine\Crud\Form\Field\ManyToMany
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
     * @return @return \Engine\Crud\Form\Field\ManyToMany
     */
    public function setOnchangeAction($onchange)
    {
        $this->_onChangeAction = $onchange;
        return $this;
    }

    /**
     * Return options array
     *
     * @params array $params
     * @return array
     */
    public function getOptions(array $params)
    {
        if (empty($this->_options)) {
            $this->_setOptions($params);
        }
        return $this->_options;
    }

    /**
     * Set options
     *
     * @return void
     */
    protected function _setOptions($params = null)
    {
        $queryBuilder = $this->_getModel()->queryBuilder();
        if ($params) {
            if (isset($params['name'])) {
                $name = $params['name'];
                $names = explode($this->_separator, $name);
                $names = \Engine\Tools\String::quote($names);
                $queryBuilder->columnsName();
                $queryBuilder->where("name IN (". $names.")");
            } else {
                if (isset($params['start'])) {
                    $offset = (int) $params['start'];
                    $queryBuilder->offset($offset);
                }
                if (isset($params['limit'])) {
                    $limit = (int) $params['limit'];
                    $queryBuilder->limit($limit);
                }
            }
        }

        $optionsFunction = (null !== $this->_fields) ? 'prepareOptionsAll' : 'prepareOptions';
        $this->_options = \Engine\Crud\Tools\Multiselect::$optionsFunction(
            $queryBuilder,
            $this->_optionName,
            $this->_category,
            $this->_categoryName,
            $this->_where,
            $this->_emptyCategory,
            $this->_emptyItem,
            true,
            $this->_fields,
            $this->_categoryOrder
        );
    }

    /**
     * Return options array
     *
     * @params array $params
     * @return array
     */
    public function getCount(array $params)
    {
        if (empty($this->_count)) {
            $this->_setCount($params);
        }
        return $this->_count;
    }

    /**
     * Set options
     *
     * @return void
     */
    protected function _setCount($params = null)
    {        
        $queryBuilder = $this->_getModel()->queryBuilder();
        if (isset($params['name'])) {
            $name = $params['name'];
            $names = explode($this->_separator, $name);
            $names = \Engine\Tools\String::quote($names);
            $queryBuilder->columnsName();
            $queryBuilder->where("name IN (".$names.")");
        }
        $queryBuilder->setColumn("COUNT(id)", "count", false);
        if ($params) {
        }
        $result = $queryBuilder->getQuery()->execute();
        $this->_count = $result[0]['count'];
    }

    /**
     * @return \Engine\Mvc\Model
     */
    protected function _getModel()
    {
        return new $this->_model;
    }

    /**
     * @return \Engine\Mvc\Model
     */
    protected function _getWorkModel()
    {
        return new $this->_workingModel;
    }
}
