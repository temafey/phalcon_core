<?php
/**
 * @namespace
 */
namespace Engine\Crud\Container\Form;

use Engine\Crud\Container\Mysql as Container,
    Engine\Crud\Container\Form\Adapter as FormContainer,
    Engine\Crud\Form,
    Engine\Crud\Form\Field,
    Engine\Mvc\Model;

/**
 * Class container for MySql.
 *
 * @category   Engine
 * @package    Crud
 * @subpackage Container
 */
class Mysql extends Container implements FormContainer
{
    /**
	 * Form object
	 * @var \Engine\Crud\Form
	 */
	protected $_form;

    /**
     * @var array
     */
    protected $_fields = [];
	
	/**
     * Constructor
     *
     * @param mixed $options
     * @return void
     */
	public function __construct(Form $form, $options = [])
	{
		$this->_form = $form;
		if (!is_array($options)) {
            $optionss = [self::MODEL => $options];
        }
		$this->setOptions($options);
	}

    /**
     * Set join models
     *
     * @param array|string $models
     * @return \Engine\Crud\Container\Form\Mysql
     */
    public function setJoinModels($models)
    {
        parent::setJoinModels($models);
        foreach ($this->_joins as $key => $model) {
            $this->_fields[$key] = $model->getAttributes();
        }

        return $this;
    }

    /**
     * Add join model
     *
     * @param string $model
     * @throws \Exception
     * @return \Engine\Crud\Container\Form\Mysql
     */
    public function addJoin($model)
    {
        $key = parent::addJoin($model);
        if ($key) {
            $this->_fields[$key] = $this->_joins[$key]->getAttributes();
        }

        return $key;
    }

    /**
     * @param $model
     */
    public function initialaizeModels()
    {
        $fields = $this->_form->getFields();
        $notRequeired = [];
        foreach ($fields as $field) {
            if ($field instanceof Field\ManyToMany || $field instanceof Field\Primary || $field instanceof Field\PasswordConfirm) {
                continue;
            }
            if ($field instanceof Field) {
                $fieldName = $field->getName();
                if (!$field->isRequire()) {
                    $notRequeired[] = $fieldName;
                }
            }
        }
        $source = $this->_model->getSource();
        $this->_model->_skipAttributes(array_intersect($this->_fields[$source], $notRequeired));
        foreach ($this->_joins as $key => $model) {
            $model->_skipAttributes(array_intersect($this->_fields[$key], $notRequeired));
        }
    }

    /**
     * Set datasource
     *
     * @return void
     */
    protected function _setDataSource()
    {
        $this->_dataSource = $this->_model->queryBuilder();

        foreach ($this->_joins as $table) {
            $this->_dataSource->columnsJoinOne($table);
        }
        $this->_dataSource->columns($this->_columns);

        foreach ($this->_conditions as $cond) {
            if (is_array($cond)) {
                $this->_dataSource->addWhere($cond['cond'], $cond['params']);
            } else {
                $this->_dataSource->addWhere($cond);
            }
        }
    }

	/**
	 * Return data array
	 * 
	 * @param int $id
	 * @return array
	 */
    public function loadData($id)
    {
        $dataSource = $this->getDataSource();
        $primary = $this->_model->getPrimary();
        $alias = $dataSource->getCorrelationName($primary);
        $dataSource->andWhere($alias.".".$primary." = '".$id."'");
        $result = $dataSource->getQuery()->execute()->toArray();

        return ($result ? $result[0] : false);
    }
	
	/**
	 * Insert new item
	 * 
	 * @param array $data
	 * @return array|integer
	 */
	public function insert(array $data)
	{
		$db = $this->_model->getWriteConnection();
		$db->begin();
		try {
            $primary = $this->_model->getPrimary();
			if (!$this->_model->create($data)) {
                $db->rollBack();
                return false;
            }
            $id = $this->_model->{$primary};
			$results = $this->_insertToJoins($id, $data);
			if (isset($results['error'])) {
			    $db->rollBack();
			    return $results;
			}
		} catch (\Engine\Exception $e) {
			return ['error' => [$e->getMessage()]];
		}
		$db->commit();
		
		return $id;
	}
	
	/**
	 * Insert new data to joins by reference id
	 * 
	 * @param string $id
	 * @param array $data
	 * @return array
	 */
	protected function _insertToJoins($id, $data)
	{
	    $results = [];
	    foreach ($this->_joins as $model) {
	        $referenceColumn = $model->getReferenceColumn($this->_model);
	        $data[$referenceColumn] = $id;
	        try {
	            $model->create($data);
                $primary = $model->getPrimary();
	            $results[] = $model->{$primary};
	        } catch (\Engine\Exception $e) {
			    return ['error' => [$e->getMessage()]];
		    }
	    }
	    
	    return $results;
	}

    /**
     * Update rows by primary id values
     *
     * @param array $id
     * @param array $data
     * @return bool|array
     */
    public function update($id, array $data)
    {
        $db = $this->_model->getWriteConnection();
        $db->begin();
        try {
            $primary = $this->_model->getPrimary();
            unset($data[$primary]);
            $record = $this->_model->findFirst($id);
            $isUpdate = false;
            $properties = get_object_vars($record);
            foreach ($data as $key => $value) {
                if (array_key_exists($key, $properties)) {
                    $isUpdate = true;
                    $record->{$key} = $value;
                }
            }
            if ($isUpdate && !$record->update()) {
                $db->rollBack();
                $messages = [];
                foreach ($record->getMessages() as $message)  {
                    $messages[] = $message->getMessage();
                }
                return ['error' => $messages];
            }
            $results = $this->_updateJoins($id, $data);
            if (isset($results['error'])) {
                $db->rollBack();
                return $results;
            }
        } catch (\Engine\Exception $e) {
            $db->rollBack();
            return ['error' => [$e->getMessage()]];
        }
        $db->commit();

        return true;
    }

    /**
     * Update data to joins tables by reference ids
     *
     * @param integer|string $id
     * @param array $data
     * @return bool|array
     */
    protected function _updateJoins($id, array $data)
    {
        try {
            foreach ($this->_joins as $model) {
                $referenceColumn = $model->getReferenceFields($this->_model);
                if (!$referenceColumn) {
                    continue;
                }
                $records = $model->findByColumn($referenceColumn, [$id]);
                foreach ($records as $record) {
                    $isUpdate = false;
                    $properties = get_object_vars($record);
                    foreach ($data as $key => $value) {
                        if (array_key_exists($key, $properties)) {
                            $isUpdate = true;
                            $record->$key = $value;
                        }
                    }
                    if ($isUpdate && !$record->update()) {
                        $messages = [];
                        foreach ($record->getMessages() as $message)  {
                            $messages[] = $message->getMessage();
                        }
                        return ['error' => $messages];
                    }
                }
            }
        } catch (\Engine\Exception $e) {
            return ['error' => [$e->getMessage()]];
        }

        return true;
    }

    /**
     * Delete rows by primary value
     *
     * @param array|string|integer $ids
     * @return bool|array
     */
    public function delete($ids)
    {
        if (!is_array($ids)) {
            $ids = [$ids];
        }
        $db = $this->_model->getWriteConnection();
        $db->begin();
        try {
            $records = $this->_model->findByIds($ids);
            foreach ($records as $record) {
                if (!$record->delete()) {
                    $db->rollBack();
                    $messages = [];
                    foreach ($record->getMessages() as $message)  {
                        $messages[] = $message->getMessage();
                    }
                    return ['error' => $messages];
                }
            }
        } catch (\Engine\Exception $e) {
            $db->rollBack();
            return ['error' => [$e->getMessage()]];
        }
        $db->commit();

        return true;
    }
}