<?php
/**
 * @namespace
 */
namespace Engine\Crud\Container\Grid;

use Engine\Crud\Container\Mysql as Container,
    Engine\Crud\Container\Grid\Adapter as GridContainer,
    Engine\Crud\Grid,
	Engine\Mvc\Model,
    Engine\Mvc\Model\Query\Builder;

/**
 * Class container for Mysql.
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
        if (null === $sort) {
            $sort = $this->_model->getOrderExpr();
        }
        $direction = $this->_grid->getSortDirection();
        if (null === $direction) {
            $direction = ($this->_model->getOrderAsc()) ? "ASC" : "DESC";
        }
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
        $extraLimit = $this->_grid->getExtraLimit();

        $page = $this->_grid->getPage();
        $extraPage = (int) ceil(($limit*$page)/$extraLimit);
		$paginator = $this->_getPaginator($dataSource, $extraLimit, $extraPage);

        $items = [];
        $position = $limit*($page-1)-($extraLimit*($extraPage-1));
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
        $filter->setDi($this->getDi());

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
	protected function _getPaginator($queryBuilder, $limit, $page)
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
                    $messages = [];
                    foreach ($record->getMessages() as $message)  {
                        $messages[] = $message->getMessage();
                    }
                    return ['error' => $messages];
                }
            }
			$results = $this->_updateJoins($ids, $data);
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