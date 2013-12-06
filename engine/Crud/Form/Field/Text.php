<?php
/**
 * @namespace
 */
namespace Engine\Crud\Form\Field;
	
/**
 * Text field
 *
 * @category   Engine
 * @package    Crud
 * @subpackage Form
 */
class Text extends AbstractField 
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
	 * @param integer $width
	 * @param integer $length
	 */
	public function __construct(
        $label = null,
        $name = false,
        $desc = null,
        $required = false,
        $width = 200,
        $default = null,
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