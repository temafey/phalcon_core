<?php
/**
 * @namespace
 */
namespace Engine\Crud\Grid\Column;

use Engine\Crud\Grid;
	
/**
 * Standart column
 *
 * @uses       \Engine\Crud\Grid\Exception
 * @uses       \Engine\Crud\Grid\Filter
 * @uses       \Engine\Crud\Grid
 * @category   Engine
 * @package    Crud
 * @subpackage Grid
 */
class Date extends Base
{
    /**
     * Column type.
     * @var string
     */
    protected $_type = 'date';

    /**
     * Sort direction
     * @var string
     */
    protected $_sortDirection = Grid::DIRECTION_DESC;

    /**
     * Date format
     * @var string
     */
    protected $_format;


    /**
     * Constructor
     *
     * @param string $title
     * @param string $name
     * @param bool $isSortable
     * @param string $format
     * @param bool $isHidden
     * @param int $width
     */
    public function __construct(
        $title,
        $name = null,
        $isSortable = true,
        $format = 'Y-m-d H:i:s',
        $isHidden = false,
        $width = 160,
        $isEditable = true,
        $fieldKey = null
    ) {
        parent::__construct($title, $name, $isSortable, $isHidden, $width, $isEditable, $fieldKey);
        $this->_format = $format;
    }

    /**
     * Set date format string
     *
     * @param string $format
     * @return \Engine\Crud\Grid\Column\Date
     */
    public function setFormat($format)
    {
        $this->_format = $format;
        return $this;
    }

    /**
     * Return date format string
     *
     * @return string
     */
    public function getFormat()
    {
        return $this->_format;
    }

    /**
     * Return render value
     *
     * @param mixed $row
     * @return string
     */
	public function render($row)
	{
		$value = $row[$this->_key];
		$value = $this->filter($value);
		$timestamp = strtotime($value);

		return date($this->_format, $timestamp);
	}
}