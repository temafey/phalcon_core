<?php
/**
 * @namespace
 */
namespace Engine\Crud\Form\Field;

/**
 * Phone field
 *
 * @category   Engine
 * @package    Crud
 * @subpackage Form
 */
class Phone extends AbstractField
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

		$this->_validators[] = array(
			'validator' => 'Regex',
			'options' => [
				'pattern' => '/^[ +()_-\d]{6,}(\s(x|ext\.?)\s?\d{3,4})?$/i'
			]
		);
	}
}
