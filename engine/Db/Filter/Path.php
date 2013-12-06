<?php
/**
 * @namespace
 */
namespace Engine\Db\Filter;

use \Engine\Mvc\Model\Query\Builder;

/**
 * Join path filter
 *
 * @category   Engine
 * @package    Db
 * @subpackage Filter
 */
class Path extends AbstractFilter 
{
	/**
	 * Join path
	 * @var string|arrat
	 */
	protected $_path;
	
	/**
	 * Filter
	 * @var \Engine\Db\Filter\AbstractFilter
	 */
	protected $_filter;

	/**
	 * Constructor
	 * 
	 * @param  $path
	 * @param AbstractFilter $filter
	 */
	public function __construct($path, AbstractFilter $filter) 
	{
		$this->_path = $path;
		$this->_filter = $filter;
	}

    /**
     * Apply filter to query builder
     *
     * @param \Engine\Mvc\Model\Query\Builder $dataSource
     * @return string
     */
	public function filterWhere(Builder $dataSource)
	{
		if (!$this->_path) {
			return $this->_filter->filterWhere($dataSource);
		}
        $model = $dataSource->getModel();
		$joinPath = $model->getRelationPath($this->_path);
		$last = end($joinPath);
		$dataSource->joinPath($joinPath);
		if ($joinPath) {
			$relation = array_shift($joinPath);

            $refModel = $relation->getReferencedModel();
            $refModel = new $refModel;
            $fields = $relation->getFields();
            $refFields = $relation->getReferencedFields();
            $options = $relation->getOptions();

			$dataSourceIn = $refModel->queryBuilder();
			$aliasIn = $dataSourceIn->columnsJoinOne($joinPath);
			$dataSourceIn->setColumn($refFields);
			if ($joinPath) {
				throw new \Engine\Exception('Could not filter embedded one-to-many rule: '.reset(array_keys($joinPath)));
			}
			$this->_filter->filterWhere($dataSourceIn);
			$alias = $dataSource->getCorrelationName($fields);

			return $alias.".".$fields." IN (".$dataSourceIn->getPhql().")";
		}

		return $this->_filter->filterWhere($dataSource);
	}

}
