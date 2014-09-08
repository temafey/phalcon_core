<?php
/**
 * @namespace
 */
namespace Engine\Search\Elasticsearch\Filter;

use Engine\Search\Elasticsearch\Query\Builder,
    Engine\Crud\Grid\Filter\Field\Join;

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
     * Filter
     * @var \Engine\Crud\Grid\Filter\Field\Join
     */
    protected $_filterField;

    /**
     * Join path
     * @var string|array
     */
    protected $_path;

    /**
     * Filter
     * @var \Engine\Search\Elasticsearch\Filter\AbstractFilter
     */
    protected $_filter;

    /**
     * Filter category model
     * @var bool|string
     */
    protected $_category;

    /**
     * Constructor
     *
     * @param \Engine\Crud\Grid\Filter\Field\Join $filterField
     * @param \Engine\Search\Elasticsearch\Filter\AbstractFilter $filter
     * @param string $pathCategory
     */
    public function __construct(Join $filterField, AbstractFilter $filter, $pathCategory = false)
    {
        $this->_filterField = $filterField;
        $this->_filter = $filter;
        $this->_category = $pathCategory;
    }

    /**
     * Apply filter to query builder
     *
     * @param \Engine\Search\Elasticsearch\Query\Builder $dataSource
     * @return string
     */
    public function filter(Builder $dataSource)
    {
        $path = $this->_filterField->getPath();

        if (!$path) {
            return $this->_filter->filter($dataSource);
        }
        if (count($path) > 1) {
            $values = $this->_filterField->normalizeValues($this->_filterField->getValue());
            if (!$values) {
                return false;
            }
            $workingModel = array_shift($path);
            $workingModel = new $workingModel;
            $refModel = array_shift($path);
            $relationsRefModel = $workingModel->getRelationPath($refModel);
            if (!$relationsRefModel) {
                throw new \Engine\Exception("Did not find relations between '".get_class($workingModel)."' and '".$refModel."' for filter field '".$this->_filterField->getKey()."'");
            }
            $mainModel = $dataSource->getModel();
            $relationsMainModel = $workingModel->getRelationPath($mainModel);
            if (!$relationsMainModel) {
                throw new \Engine\Exception("Did not find relations between '".get_class($workingModel)."' and '".get_class($mainModel)."' for filter field '".$this->_filterField->getKey()."'");
            }
            $refKey = array_shift($relationsRefModel)->getFields();
            $keyParent = array_shift($relationsMainModel)->getFields();
            $queryBuilder = $workingModel->queryBuilder();
            $queryBuilder->columns([$keyParent,$refKey]);
            // if field have category model, we add each type of category like separate item values
            if ($this->_category) {
                $temp = explode("\\", $this->_category);
                $subKey = array_pop($temp);
                $name = $refKey."_".strtolower($subKey);

                $model = new $this->_category;
                $primary = $model->getPrimary();
                $model = new $refModel;
                $relationsCategoryModel = $model->getRelationPath($this->_category);
                $categoryKey = array_shift($relationsCategoryModel)->getFields();

                $queryBuilder->columnsJoinOne($refModel, [$categoryKey => $categoryKey]);
                $queryBuilder->orderBy($categoryKey.', name');
                $queryBuilder->where($refKey." IN (".implode(",", $values).")");
                $filterData = (($result = $queryBuilder->getQuery()->execute()) === null) ? [] : $result->toArray();

                $acceptedFilters = [];
                foreach ($filterData as $filter) {
                    $newKey = $name."_".$filter[$categoryKey];
                    if (!isset($item[$newKey])) {
                        $item[$newKey] = [];
                    }
                    $acceptedFilters[$newKey][] = $filter[$refKey];
                }

                $filter = new \Elastica\Query\Bool();
                foreach ($acceptedFilters as $acceptedFilterKey => $acceptedFilterValues) {
                    $filterTerms = new \Elastica\Query\Terms($acceptedFilterKey, $acceptedFilterValues);
                    $filter->addMust($filterTerms);
                }

                return $filter;
            } else {
                $name = $this->_filterField->getName();
                $key = $this->_filterField->getKey();
                $gridColumns = $this->_filterField->getGridFilter()->getGrid()->getColumns();
                if (
                    (
                        (isset($gridColums[$key]) && $column = $gridColums[$key]) ||
                        $column = $this->_filterField->getGridFilter()->getGrid()->getColumnByName($name)
                    ) &&
                    ($column instanceof \Engine\Crud\Grid\Column\JoinOne)
                ) {
                    $name .= "_id";
                }
                if (is_array($values) && count($values) > 1) {
                    $filter = new \Elastica\Query\Terms($name, $values);
                } else {
                    if (is_array($values)) {
                        $values = $values[0];
                    }
                    $filter = new \Elastica\Query\Term();
                    $filter->setTerm($name, $values);
                }

                return $filter;
            }
        } else {
            return $this->_filter->filter($dataSource);
        }
    }

}