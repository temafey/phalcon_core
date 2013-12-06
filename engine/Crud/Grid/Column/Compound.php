<?php
/**
 * @namespace
 */
namespace Engine\Crud\Grid\Column;

use Engine\Crud\Grid\AbstractGrid as Grid,
    Engine\Crud\Container\Grid as GridContainer,
    Phalcon\Filter;

/**
 * Class Compound
 *
 * @category   Engine
 * @package    Crud
 * @subpackage Grid
 */
class Compound extends AbstractColumn
{
    /**
     * Columns
     * @var array
     */
    protected $_columns = [];

    /**
     * Column render separator
     * @var string
     */
    protected $_separator;

    /**
     * Construct
     *
     * @param array $columns
     * @param string $title
     * @param string $separator
     * @param int $width
     */
    public function __construct(array $columns, $title = null, $separator = ", ", $width = 80)
    {
        parent::__construct($title, null, false, false, $width);

        $this->_columns = $columns;
        $this->_separator = $separator;
    }

    /**
     * Set grid object and init grid column key.
     *
     * @param \Engine\Crud\Grid\AbstractGrid $grid
     * @param string $key
     * @return \Engine\Crud\Grid\Column\AbstractColumn
     */
    public function init(Grid $grid, $key)
    {
        parent::init($grid, $key);
        foreach ($this->_columns as $k => $column) {
            if (!$column instanceof AbstractColumn) {
                throw new \Engine\Exception("Column '{$k}' not instance of AbstractColumn");
            }
            $column->init($grid, $k);
        }
    }


    /**
     * Return render value
     * (non-PHPdoc)
     * @see \Engine\Crud\Grid\Column\AbstractColumn::render()
     * @param mixed $row
     * @return string
     */
    public function render($row)
    {
        $code = [];
        foreach ($this->_columns as $key => $column) {
            $code[] = '<span class="row-'.$key.'">'.$column->render($row)."</span>";
        }

        return implode($this->_separator, $code);
    }

    /**
     * Return column value by key
     *
     * @param mixed $row
     * @return string|integer
     */
    public function getValue($row)
    {
        $values = [];
        foreach ($this->_columns as $column) {
            $values = $column->getValue($row);
        }

        return implode($this->_separator, $values);
    }

    /**
     * Update grid container
     *
     * @param \Engine\Crud\Container\Grid\Adapter $container
     * @return \Engine\Crud\Grid\Column\AbstractColumn
     */
    public function updateContainer(\Engine\Crud\Container\Grid\Adapter $container)
    {
        foreach ($this->_columns as $column) {
            $column->updateContainer($container);
        }

        return $this;
    }

    /**
     * Return columns
     *
     * @return array
     */
    public function getColumns() {
        return $this->_columns;
    }

    /**
     * Return column by key
     *
     * @param string$key
     * @return \Engine\Crud\Grid\Column\AbstractColumn
     */
    public function getColumn($key)
    {
        if (!isset($this->_columns[$key])) {
            return false;
        }
        return $this->_columns[$key];
    }

}