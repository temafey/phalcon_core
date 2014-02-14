<?php
/**
 * @namespace
 */
namespace Engine\Crud\Grid\Column;

use Engine\Crud\Grid\Column;

/**
 * Join many column
 *
 * @uses       \Engine\Crud\Grid\Exception
 * @uses       \Engine\Crud\Grid\Filter
 * @uses       \Engine\Crud\Grid
 * @category   Engine
 * @package    Crud
 * @subpackage Grid
 */
class JoinMany extends Column
{
	/**
	 * Join path
	 * @var string|array
	 */
	protected $_path;
	
	/**
	 * Join column
	 * @var string
	 */
	protected $_column;
	
	/**
	 * Join table order
	 * @var string
	 */
	protected $_order;
	
	/**
	 * Value separator
	 * @var string
	 */
	protected $_separator;
	
	/**
	 * Count of join rows
	 * @var integer
	 */
	protected $_count;
	
	/**
	 * No value
	 * @var string
	 */
	protected $_na = "---";
	
	protected $_left = null;
	protected $_right = null;
	protected $_tag = null;

	/**
	 * Constructor
	 * 
	 * @param string $title
	 * @param string|array $path
	 * @param string $column
	 * @param string $orderBy
	 * @param string $separator
	 * @param int $count
	 * @param int $width
	 */
	public function __construct(
        $title,
        $path = null,
        $column = null,
        $orderBy = null,
        $separator = null,
        $count = null,
        $width = 200
    ) {
		parent::__construct($title, null, true, false, $width, false, null);
		
		$this->_path = $path;
		$this->_column = $column;
		$this->_orderBy = $orderBy;
		$this->_separator = $separator;
		$this->_count = $count;
	}
	
	/**
	 * Update container data source
	 * 
	 * @param \Engine\Crud\Container\Grid\Adapter $dataSource
	 * @return \Engine\Crud\Grid\Column\JoinMany
	 */
	public function updateDataSource($dataSource)
	{
		$dataSource->columnsJoinMany($this->_path, $this->_key, $this->_column, $this->_orderBy);
		return $this;
	}

    /**
     * Update grid container
     *
     * @param \Engine\Crud\Container\Grid\Adapter $container
     * @return \Engine\Crud\Grid\Column
     */
    public function updateContainer(\Engine\Crud\Container\Grid\Adapter $container)
    {
        $container->setColumn($this->_key, $this->_name);
        return $this;
    }

    /**
     * Return render value
     * (non-PHPdoc)
     * @see \Engine\Crud\Grid\Column::render()
     * @param mixed $row
     * @return string
     */
	public function render($row)
	{
		$value = $row[$this->_key];
		$values = explode("\n", $value);
		$count = count($values);
		if(($this->_count !== false) && ($this->_count !== null)) {
			$values = array_slice($values, 0, $this->_count);
		}
		
		if (null !== $this->_tag) {
		    foreach ($values as $i => $val) {
		        if($this->_tag == '<b>' || $this->_tag == 'b') {
		            $values = "<b>" . $val ."</b>";
		        } elseif($this->_tag == '<strong>' || $this->_tag == 'strong') {
		            $values[$i] = "<strong>" . $val ."</strong>";
		        } elseif($this->_tag == '<li>' || $this->_tag == 'li') {
		            $values[$i] = "<li>" . $val ."</li>";
		        }
		    }
		}
		
		$value = implode($this->_separator, $values);
		if(($this->_count !== false) && ($this->_count !== null) && $count > $this->_count) {
			$value .= $this->_separator . "...";
		}
		if($count == 0) {
			 $value = $this->_na;
		}
		if(!empty($this->_left)) {
		    $value = $this->_left . $value;
		}
	    if(!empty($this->_right)) {
		    $value .= $this->_right;
		}
		
		return $value;
	}

    /**
     * Return column value by key
     *
     * @param mixed $row
     * @return string|integer
     */
	public function getValue($row) 
	{
		return $this->render($row);
	}

	/**
	 * Set tag for value.
	 * 
	 * @param string $tag
	 * @return void
	 */
	public function setTag($tag) 
	{
	    $this->_tag = $tag;
	}
	
	/**
	 * Set left and right tag for value.
	 * 
	 * @param string $left
	 * @param string $right
	 * @return \Engine\Crud\Grid\Column\JoinMany
	 */
	public function setLeftRightTag($left, $right) 
	{
	    $this->_left = $left;
	    $this->_right = $right;
	    return $this;
	}
	
	/**
	 * Set empty value
	 * 
	 * @param string $na
	 * @return \Engine\Crud\Grid\Column\JoinMany
	 */
	public function setEmptyValue($na)
	{
		$this->_na = $na;
		return $this;
	}
}