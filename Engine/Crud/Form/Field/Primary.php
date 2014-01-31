<?php
/**
 * @namespace
 */
namespace Engine\Crud\Form\Field;

use Engine\Crud\Form\Field;

/**
 * Numeric field
 *
 * @category   Engine
 * @package    Crud
 * @subpackage Form
 */
class Primary extends Field
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
	public function __construct($label = null, $desc = null, $width = 280)
    {
		parent::__construct($label, false, $desc, false, $width, null);
        $this->setAttrib('readonly');
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
        $this->_name = $model->getPrimary();
    }

    /**
     * Return field save data
     *
     * @return array|bool
     */
    public function getSaveData()
    {
        return false;
    }

}