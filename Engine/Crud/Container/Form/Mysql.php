<?php
/**
 * @namespace
 */
namespace Engine\Crud\Container\Form;

use Engine\Crud\Container\AbstractContainer as Container,
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
     * Datasource object
     * @var \Engine\Mvc\Model\Query\Builder
     */
    protected $_dataSource = null;

    /**
     * Data source columns
     * @var array
     */
    protected $_columns = [];

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
     * Initialize container model
     *
     * @param string $model
     * @throws \Engine\Exception
     * @return \Engine\Crud\Container\Form\Mysql
     */
    public function setModel($model = null)
    {
        if (null === $model) {
            if (null === $this->_model) {
                throw new \Engine\Exception("Container model class not set");
            }
            $model = $this->_model;
        }
        if (is_object($model)) {
            if (!$model instanceof Model) {
                throw new \Engine\Exception("Container model class '$model' does not extend Engine\Mvc\Model");
            }
            $this->_model = $model;
        }
        if (is_array($model)) {
            $primaryModel = array_shift($model);
            $this->setJoinModels($model);
            $model = $primaryModel;
        } else {
            if (!empty($this->_joins)) {
                $joins = $this->_joins;
                $this->setJoinModels($joins);
            }
        }
        if (!class_exists($model)) {
            throw new \Engine\Exception("Container model class '$model' does not exists");
        }
        if ($this->_adapter) {
            $model->setWriteConnectionService($this->_adapter);
            $model->setReadConnectionService($this->_adapter);
        }
        $this->_model = new $model;

        $source = $this->_model->getSource();
        $this->_fields[$source] = $this->_model->getAttributes();

        return $this;
    }

    /**
     * Set model adapter
     *
     * @param string $adapter
     * @return \Engine\Crud\Container\Form\Mysql
     */
    public function setAdapter($adapter = null)
    {
        if (!$adapter) {
            return $this;
        }
        $this->_adapter = $adapter;
        if ($this->_model instanceof Model) {
            $this->_model->setWriteConnectionService($this->_adapter);
            $this->_model->setReadConnectionService($this->_adapter);
        }
        foreach ($this->_joins as $model) {
            $model->setWriteConnectionService($this->_adapter);
            $model->setReadConnectionService($this->_adapter);
        }

        return $this;
    }

    /**
     * Set join models
     *
     * @param array|string $models
     * @return \Engine\Crud\Container\Form\Mysql
     */
    public function setJoinModels($models)
    {
        if (!is_array($models)) {
            $models = [$models];
        }
        foreach ($models as $model) {
            if (!is_object($model)) {
                $model = new $model;
                if ($this->_adapter) {
                    $model->setReadConnectionService($this->_adapter);
                    $model->setWriteConnectionService($this->_adapter);
                }
            }
            if (!($model instanceof Model)) {
                throw new \Engine\Exception("Container model class '$model' does not extend Engine\Mvc\Model");
            }
            $key = $model->getSource();
            $this->_joins[$key] = $model;

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
        if (!($model instanceof Model)) {
            throw new \Engine\Exception("Container model class '$model' does not extend Engine\Mvc\Model");
        }
        if (!is_object($model)) {
            $model = new $model;
            if ($this->_adapter) {
                $model->setReadConnectionService($this->_adapter);
                $model->setWriteConnectionService($this->_adapter);
            }
        }
        $key = $model->getSource();
        if (isset($this->_joins[$key])) {
            return $this;
        }
        $this->_joins[$key] = $model;

        $this->_fields[$key] = $model->getAttributes();

        if (null !== $this->_dataSource) {
            $this->_dataSource->columnsJoinOne($model);
        }

        return $this;
    }

    /**
     * @param $model
     */
    public function initialaizeModels()
    {
        $fields = $this->_form->getFields();
        $notRequeired = [];
        foreach ($fields as $field) {
            if ($field instanceof Field\ManyToMany || $field instanceof Field\Primary) {
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
        $this->_model->skipAttributes(array_intersect($this->_fields[$source], $notRequeired));
        foreach ($this->_joins as $key => $model) {
            $model->skipAttributes(array_intersect($this->_fields[$key], $notRequeired));
        }
    }

    /**
     * Set column
     *
     * @param string $key
     * @param string $name
     * @param boolean $useTableAlias
     * @param boolean $useCorrelationTableName
     * @return \Engine\Crud\Container\Form\Mysql
     */
    public function setColumn($key, $name, $useTableAlias = true, $useCorrelationTableName = false)
    {
        if (isset($this->_columns[$key])) {
            return $this;
        }
        $this->_columns[$key] = [
            $name,
            'useTableAlias' => $useTableAlias,
            'useCorrelationName' => $useCorrelationTableName
        ];
        if (null !== $this->_dataSource) {
            $this->_dataSource->setColumn($name, $key, $useTableAlias, $useCorrelationTableName);
        }

        return $this;
    }

    /**
     * Return data source object
     *
     * @return \Engine\Mvc\Model\Query\Builder
     */
    public function getDataSource()
    {
        if (null === $this->_dataSource) {
            $this->_setDataSource();
        }
        return $this->_dataSource;
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