<?php
/**
 * @namespace
 */
namespace Engine\Crud\Container\Form;

use Engine\Crud\Container\AbstractContainer as Container,
    Engine\Crud\Container\Form\Adapter as FormContainer,
    Engine\Crud\Form,
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
     * Constructor
     *
     * @param mixed $options
     * @return void
     */
	public function __construct(Form $form, $options = array())
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
     * @return \Engine\Crud\Container\Grid\Mysql
     */
    public function setModel($model = null)
    {
        if (null === $model) {
            if(null === $this->_model) {
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
            $this->_setJoinModels($model);
            $model = $primaryModel;
        } else {
            if (!empty($this->_joins)) {
                $joins = $this->_joins;
                if(!is_array($joins)) {
                    $joins = array($joins);
                }
                $this->_setJoinModels($joins);
            }
        }
        if (!class_exists($model)) {
            throw new \Engine\Exception("Container model class '$model' does not exists");
        }

        $this->_model = new $model;

        return $this;
    }

    /**
     * Set join models
     *
     * @param array $models
     * @return \Engine\Crud\Container\Grid\Mysql
     */
    public function setJoinModels(array $models)
    {
        foreach ($models as $model) {
            if (!($model instanceof Model)) {
                throw new \Engine\Exception("Container model class '$model' does not extend Engine\Mvc\Model");
            }
            if (!is_object($model)) {
                $model = new $model;
            }
            $key = $model->getSource();
            $this->_joins[$key] = $model;
        }

        return $this;
    }

    /**
     * Add join model
     *
     * @param string $model
     * @throws \Exception
     * @return Crud\Container\Grid\Mysql
     */
    public function addJoin($model)
    {
        if (!($model instanceof Model)) {
            throw new \Engine\Exception("Container model class '$model' does not extend Engine\Mvc\Model");
        }
        if (!is_object($model)) {
            $model = new $model;
        }
        $key = $model->getSource();
        if (isset($this->_joins[$key])) {
            return $this;
        }
        $this->_joins[$key] = $model;

        if (null !== $this->_dataSource) {
            $this->_dataSource->columnsJoinOne($model);
        }

        return $this;
    }

    /**
     * Set column
     *
     * @param string $key
     * @param string $name
     * @return \Egnine\Crud\Container\Grid\Mysql
     */
    public function setColumn($key, $name)
    {
        if (isset($this->_columns[$key])) {
            return $this;
        }
        $this->_columns[$key] = $name;
        if (null !== $this->_dataSource) {
            $this->_dataSource->setColumn($name, $key);
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
        $sort = $this->_grid->getSortKey();
        $direction = $this->_grid->getSortDirection();
        if (!empty($sort)) {
            if (!empty($direction)) {
                $this->_dataSource->orderBy($this->_grid->getSortKey().' '.$this->_grid->getSortDirection());
            } else {
                $this->_dataSource->orderBy($this->_grid->getSortKey());
            }
        }
    }

	/**
	 * Return data array
	 * 
	 * @param integer $id
	 * @return array
	 */
	public function loadData($id)
	{
		return $this->_model->findFirst($id)->toArray();
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
		} catch (\Exception $e) {
			return array ('error' => $e->getMessage());
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
	        } catch (\Exception $e) {
			    return ['error' => $e->getMessage()];
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
            if (!$record->update($data)) {
                $db->rollBack();
                return ['error' => $record->getMessage()];
            }
            $results = $this->_updateJoins($id, $data);
            if (isset($results['error'])) {
                $db->rollBack();
                return $results;
            }
        } catch (\Exception $e) {
            $db->rollBack();
            return ['error' => $e->getMessage()];
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
                    if (!$record->update($data)) {
                        return ['error' => $record->getMessage()];
                    }
                }
            }
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
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
                    return ['error' => $record->getMessage()];
                }
            }
        } catch (\Exception $e) {
            $db->rollBack();
            return ['error' => $e->getMessage()];
        }
        $db->commit();

        return true;
    }
}