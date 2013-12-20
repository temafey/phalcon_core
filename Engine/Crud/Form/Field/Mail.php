<?php
/**
 * @namespace
 */
namespace Engine\Crud\Form\Field;

use Engine\Crud\Form\Field;

/**
 * Mail field
 *
 * @category   Engine
 * @package    Crud
 * @subpackage Form
 */
class Mail extends Field
{
    /**
     * Form element type
     * @var string
     */
    protected $_type = 'text';

    /**
     * Error message
     * @var string
     */
    protected $_errorMessage = 'Invalid phone number';

    /**
     * Initialize field (used by extending classes)
     *
     * @return void
     */
	protected function _init()
	{
		parent::_init();
		/*$this->_validators[] = [
			'validator' => 'Regex',
			'options' => [
				'pattern' => '/^([a-zA-Z0-9_\.\-+])+@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/i',
                'messages' => "Invalid mail address"
			]
		];*/
        $this->_validators[] = 'email';
	}
}