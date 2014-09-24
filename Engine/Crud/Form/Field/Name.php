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
class Name extends Field
{
    /**
     * Form element type
     * @var string
     */
    protected $_type = 'text';
	
	/**
	 * Constructor 
	 *
     * @param string $label
	 * @param string $desc
	 * @param int $width
	 */
	public function __construct($label = null, $desc = null, $required = true, $width = 280, $default = '')
    {
		parent::__construct($label, false, $desc, $required, $width, $default);
	}

    /**
     * Initialize field (used by extending classes)
     *
     * @return void
     */
    protected function _init()
    {
        parent::_init();

        $model = $this->_form->getContainer()->getModel();
        $this->_name = $model->getNameExpr();
    }

}