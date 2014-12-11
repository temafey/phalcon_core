<?php
/**
 * @namespace
 */
namespace Engine\Search\Elasticsearch;

use Engine\Exception,
    Engine\Tools\Traits\DIaware,
    Engine\Search\Elasticsearch\Query\Builder,
    Engine\Search\Elasticsearch\Type,
    Engine\Search\Elasticsearch\Query,
    Engine\Crud\Grid,
    Engine\Crud\Grid\Filter,
    Engine\Crud\Grid\Filter\Field;

/**
 * Class Elasticsearch
 *
 * @package Search
 * @subpackage Elasticsearch
 */
class Indexer
{
    use DIaware;

    /**
     * Type name
     * @var string
     */
    protected $_name;

    /**
     * Grid model
     * @var string
     */
    protected $_grid;

    /**
     * Search adapter
     * @var string
     */
    protected $_adapter;

    /**
     * Delete index type
     * @var bool
     */
    protected $_deleteType = true;

    /**
     * Grid params
     * @var array
     */
    protected $_params = [];

    /**
     * Construct
     *
     * @param string $name
     * @param string $grid
     * @param string $adapter
     * @param array $params
     */
    public function __construct($name, $grid, $adapter = 'elastic', array $params = [])
    {
        $this->_name = $name;
        $this->_grid = $grid;
        $this->_adapter = $adapter;
        $this->_params = $params;
    }

    /**
     * Create elasticsearch index
     *
     * @return void
     */
    public function createIndex()
    {
        $index = $this->getIndex();
        if ($index->exists()) {
            return;
        }
        // Create new index
        $index->create([
            'number_of_shards' => 1,
            'number_of_replicas' => 1,
            'analysis' => [
                'analyzer' => [
                    'indexAnalyzer' => [
                        'type' => 'custom',
                        'tokenizer' => 'standard',
                        'filter' => ['lowercase', 'mySnowball']
                    ],
                    'searchAnalyzer' => [
                        'type' => 'custom',
                        'tokenizer' => 'standard',
                        'filter' => ['standard', 'lowercase', 'mySnowball']
                    ]
                ],
                'filter' => [
                    'mySnowball' => [
                        'type' => 'snowball',
                        'language' => 'english'
                    ]
                ]
            ]
        ], true);
    }

    /**
     * Initialize and return search adapter by name
     *
     * @return \Engine\Search\Elasticsearch\Client
     */
    public function getClient()
    {
        return ($this->_adapter instanceof Client ? $this->_adapter : $this->getDi()->get($this->_adapter));
    }

    /**
     * Initialize and return search index
     *
     * @return\Engine\Search\Elasticsearch\Index
     */
    public function getIndex()
    {
        return $this->getClient()->getIndex();
    }

    /**
     * Initialize and return search type
     *
     * @return Type
     */
    public function getType()
    {
        $type = new Type($this->_name);
        $type->setDi($this->getDi());
        $type->setAdapter($this->_adapter);

        return $type;
    }

    /**
     * Mapping index
     *
     * @return void
     */
    public function setMapping()
    {
        $mapping = new \Elastica\Type\Mapping();
        $mapping->setType($this->getType());
        $mapping->setParam('index_analyzer', 'indexAnalyzer');
        $mapping->setParam('search_analyzer', 'searchAnalyzer');

        // Set mapping
        $properties = [];
        $grid = ($this->_grid instanceof \Engine\Crud\Grid) ? $this->_grid : new $this->_grid($this->_params, $this->getDi());
        $filterFields = $grid->getFilter()->getFields();
        $gridColums = $grid->getColumns();
        foreach ($filterFields as $key => $field) {
            $name = $field->getName();
            $sortable = false;
            $store = false;
            $joinType = false;
            if ((isset($gridColums[$key]) && $column = $gridColums[$key]) || $column = $grid->getColumnByName($name)) {
                $sortable = $column->isSortable();
                $store = true;
                if ($column instanceof \Engine\Crud\Grid\Column\JoinOne) {
                    $joinType = true;
                }
            }
            $property = $this->getFieldMap($field, $sortable, $store, $joinType);
            if (!$property) {
                continue;
            }
            if (isset($property['type'])) {
                $properties[$name] = $property;
            } else {
                $properties += $property;
            }
        }

        $mapping->setProperties($properties);

        // Send mapping to type
        $mapping->send();
    }

    /**
     *  Build and return field map property for mapping using grid filter object
     *
     * @param \Engine\Crud\Grid\Filter\Field $field
     * @param bool $sortable
     * @param bool $store
     * @param bool $joinType
     * @return array|bool
     */
    public function getFieldMap(Field $field, $sortable = false, $store = false, $joinType = false)
    {
        $property = false;
        $key = $field->getKey();
        $name = $field->getName();
        if (
            $field instanceof Field\Match ||
            $field instanceof Field\Search ||
            $field instanceof Field\Submit
        ) {

        } elseif ($field instanceof Field\Primary) {
            $property = $this->getFieldProperty($name, 'integer', $sortable, 'analyzed', $store, true, 2.0);
        } elseif ($field instanceof Field\Date) {
            $property = $this->getFieldProperty($name, 'date', $sortable, 'analyzed', $store, true, 2.0, "YYYY-MM-dd HH:mm:ss");
        } elseif ($field instanceof Field\Between) {
            $property = $this->getFieldProperty($name, 'float', $sortable, 'analyzed', $store, true, 2.0);
        } else if ($field instanceof Field\Compound) {
            $property = [
                'type' => 'multi_field',
                'fields' => []
            ];
            $fields = $field->getFields();
            foreach ($fields as $field) {
                $property['fields'][] = $this->getFieldMap($field);
            }
        } else if ($field instanceof Field\Join) {
            $path = $field->getPath();
            if (count($path) > 1) {
                if ($field->category) {
                    $property = [];
                    $model = $field->category;
                    //$name = str_replace(["\\model", "\\"], ["", "_"], strtolower(trim($model, "\\")));
                    $name = $key;
                    $temp = explode("\\", $model);
                    $subKey = array_pop($temp);
                    $name .= "_".strtolower($subKey);
                    $model = new $model;
                    $filters = $model->find()->toArray();
                    $primary = $model->getPrimary();
                    foreach ($filters as $filter) {
                        $property[$name.'_'.$filter[$primary]] = [
                            //"index_name" => $filter['filter_key'],
                            'type' => 'integer',
                            'store' => false,
                            //'index' => 'analyzed',
                            //'index' => 'no',
                            'include_in_all' => FALSE
                        ];
                        $property[$name.'_'.$filter[$primary]] = $this->getFieldProperty($name.'_'.$filter[$primary], 'integer', false, false, false, false);
                    }
                } else {
                    $property[$key] = $this->getFieldProperty($key, 'string', $sortable, 'analyzed', $store, true, 2.0);
                    $property[$key."_id"] = $this->getFieldProperty($key."_id", 'integer', $sortable, 'analyzed', $store, true, 2.0);
                }
            } else {
                $property = [];
                if ($joinType) {
                    $property[$key] = $this->getFieldProperty($key, 'string', $sortable, 'analyzed', $store, true, 2.0);
                    $property[$key."_id"] = $this->getFieldProperty($key."_id", 'integer', $sortable, 'analyzed', $store, true, 2.0);
                } else {
                    $property[$key] = $this->getFieldProperty($key, 'integer', $sortable, 'analyzed', $store, true, 2.0);
                }
            }
        } elseif (
            $field instanceof Field\Numeric ||
            $field instanceof Field\InArray ||
            $field instanceof Field\ArrayToSelect ||
            $field instanceof Field\Checkbox
        ) {
            $property = $this->getFieldProperty($name, 'integer', $sortable, 'analyzed', $store, true, 2.0);
        } elseif (
            $field instanceof Field\Name ||
            $field instanceof Field\Standart
        ) {
            $property = $this->getFieldProperty($name, 'string', $sortable, 'analyzed', $store, true, 2.0);
        }

        return $property;
    }

    /**
     * Build field property for index mapping
     *
     * @param string $name
     * @param string $type
     * @param bool $sortable
     * @param string $index
     * @param bool $store
     * @param bool $include
     * @param float $boost
     * @param string $format
     * @return array
     */
    public function getFieldProperty(
        $name,
        $type,
        $sortable = false,
        $index = 'analyzed',
        $store = false,
        $include = true,
        $boost = 2.0,
        $format = false
    ) {
        $store = ($store) ? 'yes' : 'no';
        $field = [
            'type' => $type,
            'index' => $index,
            'store' => $store,
            'include_in_all' => $include,
            'boost' => $boost
        ];
        if ($format) {
            $field['format'] = $format;
        }
        if ($sortable) {
            $field = [
                'type' => 'multi_field',
                'fields' => [
                    $name => $field,
                    'sort' => [
                        'type' => $type,
                        'index' => 'not_analyzed',
                        'store' => 'no',
                        'include_in_all' => FALSE
                    ]
                ]
            ];
            if ($format) {
                $field['fields']['sort']['format'] = $format;
            }
        }

        return $field;
    }

    /**
     * Add data from grid to search index
     *
     * @return void
     */
    public function setData()
    {
        $type = $this->getType();
        if ($this->_deleteType && $type->exists()) {
            $type->delete();
        }
        $this->setMapping();
        $grid = ($this->_grid instanceof \Engine\Crud\Grid) ? $this->_grid : new $this->_grid([], $this->getDi());

        $config = [];
        $config['model'] = $grid->getModel();
        $config['conditions'] = $grid->getConditions();
        $config['joins'] = $grid->getJoins();
        $modelAdapter = $grid->getModelAdapter();
        if ($modelAdapter) {
            $config['modelAdapter'] = $modelAdapter;
        }
        $container = new \Engine\Crud\Container\Grid\Mysql($grid, $config);

        $columns = $grid->getColumns();
        foreach ($columns as $column) {
            $column->updateContainer($container);
        }
        $dataSource = $container->getDataSource();
        foreach ($columns as $column) {
            $column->updateDataSource($dataSource);
        }
        $filter = $grid->getFilter();
        $params = $grid->getFilterParams();
        $filter->setParams($params);
        $filter->applyFilters($dataSource);

        $i = 0;
        $pages = false;
        do {
            ++$i;
            $grid->clearData();
            $grid->setParams(['page' => $i]);
            $data = $container->getData($dataSource);
            if (!$pages) {
                $pages = $data['pages'];
            }
            foreach ($data['data'] as $values) {
                $this->addItem($values->toArray(), $grid);
            }
        } while ($i < $pages);
    }

    /**
     * Add new item to index
     *
     * @param array $data
     * @param \Engine\Crud\Grid $grid
     * @throws \Engine\Exception
     */
    public function addItem(array $data, $grid = null)
    {
        if (!$grid) {
            $grid = new $this->_grid([], $this->getDi());
        }
        $itemDocument = $this->_processItemData($data, $grid);
        if (!$itemDocument) {
            return;
        }

        return $this->getType()->addDocument($itemDocument);
    }

    /**
     * Update document in index
     *
     * @param array $data
     * @param \Engine\Crud\Grid $grid
     * @throws \Engine\Exception
     */
    public function updateItem($data, $grid = null)
    {
        if (!$grid) {
            $grid = new $this->_grid([], $this->getDi());
        }
        $itemDocument = $this->_processItemData($data, $grid);

        return $this->getType()->updateDocument($itemDocument);
    }

    /**
     * Delete document from index
     *
     * @param array $data
     * @param \Engine\Crud\Grid $grid
     * @throws \Engine\Exception
     */
    public function deleteItem($data, $grid = null)
    {
        if (!$grid) {
            $grid = new $this->_grid([], $this->getDi());
        }
        $itemDocument = $this->_processItemData($data, $grid);

        return $this->getType()->deleteDocument($itemDocument);
    }

    /**
     * Build elastica document
     *
     * @param array $data
     * @param \Engine\Crud\Grid $grid
     * @return \Elastica\Document
     * @throws \Engine\Exception
     */
    protected function _processItemData(array $data, \Engine\Crud\Grid $grid)
    {
        $primaryKey = $grid->getPrimaryColumn()->getName();
        $filterFields = $grid->getFilter()->getFields();
        $gridColums = $grid->getColumns();

        $item = [];
        foreach ($filterFields as $key => $field) {
            if (
                $field instanceof Field\Search ||
                $field instanceof Field\Compound ||
                $field instanceof Field\Match ||
                $field instanceof Field\Submit
            ) {
                continue;
            }
            // check if filter field is a join field
            if ($field instanceof Field\Join) {
                $this->_processJoinFieldData($item, $key, $field, $data, $grid);
            } elseif ($field instanceof Field\Date) {
                $this->_processDateFieldData($item, $key, $field, $data, $grid);
            } else {
                $this->_processStandartFieldData($item, $key, $field, $data, $grid);
            }
        }

        if (!(isset($item[$primaryKey]))) {
            $item[$primaryKey] = $data[$primaryKey];
        }
        $id = $item[$primaryKey];

        return $itemDocument = new \Elastica\Document($id, $item);
    }

    /**
     * Process data in standart filter type field value for search document
     *
     * @param array $item
     * @param string $key
     * @param \Engine\Crud\Grid\Filter\Field $field
     * @param array $data
     * @param \Engine\Crud\Grid $grid
     * @throws \Exception
     * @return void
     */
    protected function _processStandartFieldData(array &$item, $key, Field $field, array $data, Grid $grid)
    {
        $name = $field->getName();
        $dataKey = $grid->getColumnByName($name)->getKey();
        if (!array_key_exists($dataKey, $data)) {
            throw new \Engine\Exception("Value by filter key '".$dataKey."' not found in data from grid '".get_class($grid)."'");
        }
        $item[$name] = $data[$dataKey];
    }

    /**
     * Process data in date filter type field value for search document
     *
     * @param array $item
     * @param string $key
     * @param \Engine\Crud\Grid\Filter\Field $field
     * @param array $data
     * @param \Engine\Crud\Grid $grid
     * @throws \Exception
     * @return void
     */
    protected function _processDateFieldData(array &$item, $key, Field\Date $field, array $data, Grid $grid)
    {
        $name = $field->getName();
        $dataKey = $grid->getColumnByName($name)->getKey();
        $item[$name] = $data[$dataKey];
    }

    /**
     * Process data field value for search document
     *
     * @param array $item
     * @param string $key
     * @param \Engine\Crud\Grid\Filter\Field $field
     * @param array $data
     * @param \Engine\Crud\Grid $grid
     * @throws \Exception
     * @return void
     */
    protected function _processJoinFieldData(array &$item, $key, Field\Join $field, array $data, Grid $grid)
    {
        $name = $field->getName();
        $path = $field->getPath();
        $primaryKey = $grid->getPrimaryColumn()->getName();
        // if count of path models more than one, means that is many to many relations
        if (count($path) > 1) {
            $workingModelClass = array_shift($path);
            $workingModel = new $workingModelClass;
            $refModelClass = array_shift($path);
            $refModel = new $refModelClass;
            $relationsRefModel = $workingModel->getRelationPath($refModel);
            if (!$relationsRefModel) {
                throw new \Engine\Exception("Did not find relations between '".get_class($workingModel)."' and '".$refModel."' for filter field '".$key."'");
            }
            $mainModel = $grid->getContainer()->getModel();
            $relationsMainModel = $workingModel->getRelationPath($mainModel);
            if (!$relationsMainModel) {
                throw new \Engine\Exception("Did not find relations between '".get_class($workingModel)."' and '".get_class($mainModel)."' for filter field '".$key."'");
            }
            $refKey = array_shift($relationsRefModel)->getFields();
            $keyParent = array_shift($relationsMainModel)->getFields();
            $queryBuilder = $workingModel->queryBuilder();
            $db = $workingModel->getReadConnection();
            $queryBuilder->columns([$keyParent,$refKey]);
            // if field have category model, we add each type of category like separate item values
            if ($field->category) {
                $category = $field->category;

                $temp = explode("\\", $category);
                $subKey = array_pop($temp);
                $name .= "_".strtolower($subKey);

                $model = new $category;
                $primary = $model->getPrimary();
                $relationsCategoryModel = $refModel->getRelationPath($category);
                $categoryKey = array_shift($relationsCategoryModel)->getFields();

                $queryBuilder->columnsJoinOne($refModelClass, [$categoryKey => $categoryKey]);
                $queryBuilder->orderBy($categoryKey.', name');
                $queryBuilder->andWhere($keyParent." = '".$data[$primaryKey]."'");
                $sql = $queryBuilder->getPhql();
                $sql = str_replace(
                    [trim($workingModelClass, "\\"), trim($refModelClass, "\\"), "[", "]"],
                    [$workingModel->getSource(), $refModel->getSource(), "", ""],
                    $sql
                );
                $filterData = $db->fetchAll($sql);
                //$filterData = (($result = $queryBuilder->getQuery()->execute()) === null) ? [] : $result->toArray();

                foreach ($filterData as $filter) {
                    $newName = $name."_".$filter[$categoryKey];
                    if (!isset($item[$newName])) {
                        $item[$newName] = [];
                    }
                    $item[$newName][] = $filter[$refKey];
                }
            } else {
                $queryBuilder->andWhere($keyParent." = '".$data[$primaryKey]."'");
                $queryBuilder->columnsJoinOne($refModel, ['name' => 'name', 'id' => 'id']);
                $queryBuilder->orderBy('name');
                $sql = $queryBuilder->getPhql();
                $sql = str_replace(
                    [trim($workingModelClass, "\\"), trim($refModelClass, "\\"), "[", "]"],
                    [$workingModel->getSource(), $refModel->getSource(), "", ""],
                    $sql
                );
                $savedData = $db->fetchAll($sql);
                //$savedData = (($result = $queryBuilder->getQuery()->execute()) === null) ? [] : $result->toArray();
                $item[$key] = \Engine\Tools\Arrays::assocToLinearArray($savedData, 'name');
                $item[$key."_id"] = \Engine\Tools\Arrays::assocToLinearArray($savedData, 'id');
                //$item[$key] = \Engine\Tools\Arrays::resultArrayToJsonType($savedData);
            }
        } else {
            if (
                (
                    (isset($gridColums[$key]) && $column = $gridColums[$key]) ||
                    $column = $grid->getColumnByName($name)
                ) &&
                ($column instanceof \Engine\Crud\Grid\Column\JoinOne)
            ) {
                $item[$key] = [];
                $item[$key] = $data[$key];
                $item[$key."_id"] = $data[$key."_".\Engine\Mvc\Model::JOIN_PRIMARY_KEY_PREFIX];
            } else {
                $item[$key] = $data[$key];
            }
        }
    }

    /**
     * Delete index
     *
     * @return \Elastica\Response
     */
    public function deleteIndex()
    {
        $index = $this->getIndex();
        if (!$index->exists()) {
            return false;
        }
        return $index->delete();
    }

    /**
     * Set flag to delete index type before add new
     *
     * @param bool $deleteType
     * @return $this
     */
    public function setDeleteIndexType($deleteType)
    {
        $this->_deleteType = (bool) $deleteType;
        return $this;
    }
}