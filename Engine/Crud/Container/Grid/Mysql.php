<?php
/**
 * @namespace
 */
namespace Engine\Crud\Container\Grid;

use Engine\Crud\Container\AbstractContainer as Container,
    Engine\Crud\Container\Grid\Adapter as GridContainer,
    Engine\Crud\Grid,
	Engine\Mvc\Model,
    Engine\Mvc\Model\Query\Builder;

/**
 * Class container for MySql.
 *
 * @category   Engine
 * @package    Crud
 * @subpackage Container
 */
class Mysql extends Container implements GridContainer
{	
	/**
	 * Grid object
	 * @var \Engine\Crud\Grid
	 */
	protected $_grid;
	
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
     * @param \Engine\Crud\Grid $grid
     * @param array $options
     */
    public function __construct(\Engine\Crud\Grid $grid, $options = [])
	{
		$this->_grid = $grid;
		if (!is_array($options)) {
            $options = [self::MODEL => $options];
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
					$joins = [$joins];
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
	 * @return \Engine\Crud\Container\Grid\Mysql
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
	 * @return \Engine\Crud\Container\Grid\Mysql
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
        if ($sort) {
            $alias = $this->_dataSource->getCorrelationName($sort);
            if ($alias) {
                $sort = $alias.".".$sort;
            }
        	if ($direction) {
        		$this->_dataSource->orderBy($sort.' '.$direction);
        	} else {
        		$this->_dataSource->orderBy($sort);
        	}
        }
	}
	
	/**
	 * Return data array
	 * 
	 * @return array
	 */
	public function getData($dataSource)
	{
		$limit = $this->_grid->getLimit();
        //$extraLimit = $this->_grid->getExtraLimit();
        $extraLimit = 100;
        $page = $this->_grid->getPage();
        $extraPage = (int) ceil(($limit*$page)/$extraLimit);
		$paginator = $this->_getPaginator($dataSource, $extraLimit, $extraPage);
        $items = [];
        $position = $limit*($page-1);
        if ($paginator->total_items > 0) {
            for ($i = $position; $i < $position+$limit; ++$i) {
                if (!isset($paginator->items[$i])) {
                    break;
                }
                $items[] = $paginator->items[$i];
            }
        }
	    $data = [
	    	'data' => $items,
	    	'page' => $page,
	    	'limit' => $limit,
	    	'mess_now' => count($items)
	    ];
	    
	    if ($this->_grid->isCountQuery()) {
	    	$data['pages'] = (int) ceil($paginator->total_items/$limit);
	    	$data['lines'] = $paginator->total_items;
	    }
	    
	    return $data;
	}
	
	/**
	 * Return filter object
	 *
	 * @return \Engine\Filter\SearchFilterInterface
	 */
	public function getFilter()
	{
		$args = func_get_args();
		$type = array_shift($args);
		$className = $this->getFilterClass($type);
		$rc = new \ReflectionClass($className);
		$filter = $rc->newInstanceArgs($args);
        $filter->setDi($this->_model->getDi());

		return $filter;
	}
	
	/**
	 * Return filter class name
	 * 
	 * @param string $type
	 * @return string
	 */
	public function getFilterClass($type)
	{
		return '\Engine\Db\Filter\\'.ucfirst($type);
	}
	
	/**
	 * Setup paginator.
	 * 
	 * @param \Engine\Mvc\Model\Query\Builder $queryBuilder
	 * @return \ArrayObject
	 */
	protected function _getPaginator(\Engine\Mvc\Model\Query\Builder $queryBuilder, $limit, $page)
    {
        $paginator = new \Phalcon\Paginator\Adapter\QueryBuilder([
            'builder' => $queryBuilder,
            'limit' => $limit,
            'page' => $page
        ]);

    	return $paginator->getPaginate();
	}
	
	/** 
	 * Update rows by primary id values
	 * 
	 * @param array $id
	 * @param array $data
	 * @return bool|array
	 */
	public function update(array $ids, array $data)
	{
        $db = $this->_model->getWriteConnection();
		$db->begin();
	    try {
	        $primary = $this->_model->getPrimary();
			unset($data[$primary]);
            $records = $this->_model->findByIds($ids);
            foreach ($records as $record) {
                if (!$record->update($data)) {
                    $db->rollBack();
                    return ['error' => $record->getMessage()];
                }
            }
			$results = $this->_updateJoins($ids, $data);
	        if (isset($results['error'])) {
	            $db->rollBack();
			    return $results;
			}
		} catch (\Engine\Exception $e) {
            $db->rollBack();
			return ['error' => $e->getMessage()];
		}
		$db->commit();
		
		return true;
	}
	
	/**
	 * Update data to joins tables by reference ids
	 * 
	 * @param array $ids
	 * @param array $data
     * @return bool|array
	 */
	protected function _updateJoins(array $ids, array $data)
	{
        try {
            foreach ($this->_joins as $model) {
                $referenceColumn = $model->getReferenceFields($this->_model);
                if (!$referenceColumn) {
                    continue;
                }
                $records = $model->findByColumn($referenceColumn, $ids);
                foreach ($records as $record) {
                    if (!$record->update($data)) {
                        return ['error' => $record->getMessage()];
                    }
                }
            }
        } catch (\Engine\Exception $e) {
            return ['error' => $e->getMessage()];
        }
	    
	    return true; 
	}
	
	/**
	 * Delete rows by primary value
	 * 
	 * @param array $ids
	 * @return bool|array
	 */
	public function delete(array $ids)
	{
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
        } catch (\Engine\Exception $e) {
            $db->rollBack();
            return ['error' => $e->getMessage()];
        }
        $db->commit();

        return true;
	}
}