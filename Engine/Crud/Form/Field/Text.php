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
	protected $_length;
	
	/**
	 * Field constructor
	 *
     * @param string $label
	 * @param string $name
	 * @param string $desc
	 * @param string $criteria
	 * @param int $width
	 * @param int $length
	 */
	public function __construct(
        $label = null,
        $name = null,
        $desc = null,
        $required = false,
        $width = 280,
        $default = '',
        $length = 255
    ) {
		parent::__construct($label, $name, $desc, $required, $width, $default);

		$this->_length = (int) $length;
	}

    /**
     * Initialize field (used by extending classes)
     *
     * @return void
     */
	protected function _init()
	{
        parent::_init();

        $this->_validators[] = [
            'validator' => 'StringLength',
            'options' => [
                'max' => $this->_length
            ]
        ];
	}	
}