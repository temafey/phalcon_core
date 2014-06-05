<?php
/**
 * @namespace
 */
namespace Engine\Crud\Form\Field;

use Engine\Crud\Form\Field;

/**
 * Text field
 *
 * @category   Engine
 * @package    Crud
 * @subpackage Form
 */
class Text extends Field
{
    /**
     * Form element type
     * @var string
     */
    protected $_type = 'text';

    /**
     * Max string length
     * @var integer
     */
    protected $_lengthMax;

    /**
     * Min string length
     * @var integer
     */
    protected $_lengthMin;

    /**
     * Field constructor
     *
     * @param string $label
     * @param string $name
     * @param string $desc
     * @param string $criteria
     * @param int $width
     * @param int $lengthMax
     * @param int $lengthMin
     */
    public function __construct(
        $label = null,
        $name = null,
        $desc = null,
        $required = false,
        $width = 280,
        $default = '',
        $lengthMax = 255,
        $lengthMin = false
    ) {
        parent::__construct($label, $name, $desc, $required, $width, $default);

        $this->_lengthMax = (int) $lengthMax;
        $this->_lengthMin = (int) $lengthMin;
    }

    /**
     * Initialize field (used by extending classes)
     *
     * @return void
     */
    protected function _init()
    {
        parent::_init();

        if ($this->_lengthMax || $this->_lengthMin) {
            $options = [];
            if ($this->_lengthMax) {
                $options['max'] = $this->_lengthMax;
            }
            if ($this->_lengthMin) {
                $options['min'] = $this->_lengthMin;
            }
            $this->_validators[] = [
                'validator' => 'StringLength',
                'options' => $options
            ];
        }
    }
}