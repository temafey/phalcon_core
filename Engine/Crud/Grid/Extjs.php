<?php
/**
 * @namespace
 */
namespace Engine\Crud\Grid;

use Engine\Crud\Grid;

/**
 * Class Extjs.
 *
 * @category   Engine
 * @package    Crud
 * @subpackage Grid
 */
abstract class Extjs extends Grid
{
    /**
     * Default decorator
     */
    const DEFAULT_DECORATOR = 'Extjs';

    /**
     * Content managment system module router prefix
     * @var string
     */
    protected $_modulePrefix = 'cms';

    /**
     * Extjs module name
     * @var string
     */
    protected $_module;

    /**
     * Extjs grid key
     * @var string
     */
    protected $_key;

    /**
     * Grid height
     * @var int
     */
    protected $_height = 400;

    /**
     * Grid edititng type (row,cell,false)
     * @var string
     */
    protected $_editType = 'row';

    /**
     * Get grid action
     *
     * @return string
     */
    public function getModulePrefix()
    {
        return $this->_modulePrefix;
    }

    /**
     * Return extjs module name
     *
     * @return string
     */
    public function getModuleName()
    {
        return $this->_module;
    }

    /**
     * Return extjs grid key
     *
     * @return string
     */
    public function getKey()
    {
        return $this->_key;
    }

    /**
     * Get grid action
     *
     * @return string
     */
    public function getAction()
    {
        if (!empty($this->_action)) {
            return $this->_action;
        }
        return $this->_modulePrefix."/".$this->getModuleName()."/".$this->getKey();
    }

    /**
     * Return grid fetching data
     *
     * @return array
     */
    public function getData()
    {
        if (null === $this->_data) {
            $this->_setData();
        }

        return json_encode($this->_data);
    }

    /**
     * Return datas with rendered values.
     *
     * @return array
     */
    public function getDataWithRenderValues()
    {
        if (null === $this->_data) {
            $this->_setData();
        }
        $data = $this->_data;
        foreach ($data[$this->_key] as $i => $row) {
            $values = [];
            foreach ($this->_columns as $key => $column) {
                $values[$key] = $column->render($row);
            }
            $data[$this->_key][$i] = $values;
        }

        return json_encode($data);
    }

    /**
     * Set grid params
     *
     * @param array $params
     * @return \Engine\Crud\Grid
     */
    public function setParams(array $params)
    {
        $sort = $this->getSortParamName();
        $direction = $this->getSortDirectionParamName();
        if (isset($params[$sort])) {
            if (\Engine\Tools\String::isJson($params[$sort])) {
                $sortParams = json_decode($params[$sort])[0];
                $params[$sort] = $sortParams->property;
                $params[$direction] = $sortParams->direction;
            }
            $this->_sortParamValue = $params[$sort];
        }
        if (isset($params[$direction])) {
            $this->_directionParamValue = $params[$direction];
        }
        $limit = $this->getLimitParamName();
        if (isset($params[$limit])) {
            $this->_limitParamValue = $params[$limit];
        }
        $page = $this->getPageParamName();
        if (isset($params[$page])) {
            $this->_pageParamValue = $params[$page];
        }
        $this->_params = $params;

        return $this;
    }

    /**
     * Return current sort params
     *
     * @param bool $withFilterParams
     * @return array
     */
    public function getSortParams($withFilterParams = true)
    {
        if (null !== $this->_sortParamValue && isset($this->_columns[$this->_sortParamValue])) {
            return $this->_columns[$this->_sortParamValue]->getSortParams($withFilterParams);
        }
        if ($withFilterParams) {
            return $this->getFilterParams();
        }
        return [];
    }

    /**
     * Paginate data array
     *
     * @param array $data
     * @return void
     */
    protected function _paginate(array $data)
    {
        $this->_data[$this->_key] = $data['data'];
        unset($data['data']);
        $page = $data['page'];
        $limit = $data['limit'];

        if ($page == 'all' || $limit == 'all' || $limit === false) {
            $this->_data['results'] = count($data['data']);
            return true;
        }

        $lines = ($this->_isCountQuery) ? $data['lines'] : $limit;
        $this->_data['results'] = $lines;

        return true;
    }

    /**
     * Do something before render
     *
     * @return string
     */
    protected function _beforeRender()
    {

    }

    /**
     * Return grid width
     *
     * @return integer
     */
    public function getWidth()
    {
        $width = 20;
        foreach ($this->_columns as $column) {
            $width += $column->getWidth();
        }

        return $width;
    }

    /**
     * Return grid height
     *
     * @return integer
     */
    public function getHeight()
    {
        return $this->_height;
    }

    /**
     * Return grid editing type
     *
     * @return string
     */
    public function getEditingType()
    {
        return $this->_editType;
    }
} 