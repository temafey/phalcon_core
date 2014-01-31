<?php
/**
 * @namespace
 */
namespace Engine\Crud\Grid\Column;

use Engine\Crud\Grid\Column,
    Engine\Crud\Grid,
    Engine\Crud\Container\Grid as GridContainer,
	Phalcon\Filter;
	
/**
 * Class Text
 *
 * @category   Engine
 * @package    Crud
 * @subpackage Grid
 */
class Status extends Collection
{
    /**
     * Column type.
     * @var string
     */
    protected $_type = 'check';

    /**
     * Constructor
     *
     * @param string $title
     * @param string $name
     * @param array $options
     * @param bool $isSortable
     * @param bool $isHidden
     * @param int $width
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
        parent::__construct($title, $name, $options, $isSortable, $isHidden, $width, $isEditable, $fieldKey);
    }
}