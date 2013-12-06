<?php
/**
 * @namespace
 */
namespace Engine\Crud\Form\Field;

/**
 * Form field
 *
 * @category   Engine
 * @package    Crud
 * @subpackage Form
 */
class ArrayToRadio extends ArrayToSelect
{
    /**
     * Element type
     * @var string
     */
    protected $_type = 'multiCheckbox';

    /**
     * Null option
     * @var string|array
     */
    protected $_nullOption = false;

}
