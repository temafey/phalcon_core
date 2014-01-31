<?php
/**
 * @namespace
 */
namespace Engine\Crud\Form\Field;

/**
 * Textarea field
 *
 * @category   Engine
 * @package    Crud
 * @subpackage Form
 */
class TextArea extends Text 
{
    /**
     * Form element type
     * @var string
     */
    protected $_type = 'textarea';

	/**
	 * Textarea input height
	 * @var integer
	 */
	protected $_height;
	
	/**
	 * Textarea input rows
	 * @var integer
	 */
	protected $_rows;
	
	/**
	 * Textarea input columns
	 * @var integer
	 */
	protected $_cols;

    /**
     * @param bool $name
     * @param string $label
     * @param string $desc
     * @param bool $required
     * @param int $width
     * @param string $default
     * @param bool $length
     * @param int $height
     * @param int $rows
     * @param int $cols
     */
    public function __construct(
        $label = null,
        $name = false,
        $desc = null,
        $required = false,
        $width = 280,
        $default = null,
        $length = false,
        $height = 100,
        $rows = 4,
        $cols = 30
    ) {
		parent::__construct($label, $name, $desc, $required, $width, $default, $length);

		$this->_height = intval($height);
		$this->_rows = $rows;
		$this->_cols = $cols;
	}

    /**
     * Initialize field (used by extending classes)
     *
     * @return void
     */
	protected function _init()
	{
		parent::_init();
		$this->setAttrib('height', $this->_height);
		$this->setAttrib('rows', $this->_rows);
		$this->setAttrib('cols', $this->_cols);
	}
}