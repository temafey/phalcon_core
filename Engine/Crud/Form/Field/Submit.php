<?php
/**
 * @namespace
 */
namespace Engine\Crud\Form\Field;

use Engine\Crud\Form\Field;

/**
 * Submit Form field
 *
 * @category   Engine
 * @package    Crud
 * @subpackage Grid
 */
class Submit extends Field
{
	protected $_type = 'submit';
	
	/**
     * Constructor
	 *
	 * @param string $title
	 * @param integer $width
	 */
	public function __construct($label = null, $width = 60)
	{
        $this->_label = $label;
        $this->_width = intval($width);
	}

	/**
     * Initialize field (used by extending classes)
     *
     * @return void
     */
	protected function _init()
	{
	}
}
