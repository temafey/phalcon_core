<?php
/**
 * @namespace
 */
namespace Engine\Crud\Grid\Column;

/**
 * Class Collection
 *
 * @uses       \Engine\Crud\Grid\Exception
 * @uses       \Engine\Crud\Grid\Filter
 * @uses       \Engine\Crud\Grid
 * @category   Engine
 * @package    Crud
 * @subpackage Grid
 */
class Collection extends Base
{
    /**
     * Field type.
     * @var string
     */
    protected $_type = 'collection';

	/**
	 * Collection option array
	 * @var array
	 */
	protected $_options = [];
	
	/**
	 * Empty value
	 * @var string
	 */
	protected $_na = "-";

    /**
     * Constructor
     *
     * @param string $title
     * @param string $name
     * @param array $options
     * @param bool $isSortable
     * @param bool $isHidden
     * @param int $width
     * @param bool $isEditable
     * @param string $fieldKey
     */
    public function __construct(
        $title,
        $name = null,
        array $options = [],
        $isSortable = true,
        $isHidden = false,
        $width = 200,
        $isEditable = true,
        $fieldKey = null
    ) {
		parent::__construct($title, $name, $isSortable, $isHidden, $width, $isEditable, $fieldKey);
		$this->_options = $options;
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
		$value = parent::render($row);
		if ($value !== '') {
			if (isset($this->_options[$value])) {
				$value = $this->_options[$value];
			}
		} else {
			$value = $this->_na;
		}
		
		return $value;
	}
	
	/**
	 * Set empty value
	 * 
	 * @param string $na
	 * @return \Engine\Crud\Grid\Column\Collection
	 */
	public function setEmptyValue($na)
	{
		$this->_na = $na;
		return $this;
	}
	
	/**
	 * Set column options array
	 * 
	 * @param array $options
	 * @return \Engine\Crud\Grid\Column\Collection
	 */
	public function setOptions(array $options)
	{
	    $this->_options = $options;
	    return $this;
	}

    /**
     * Return collection options
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->_options;
    }
}