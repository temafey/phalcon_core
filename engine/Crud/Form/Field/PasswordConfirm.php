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
class PasswordConfirm extends AbstractField
{
    /**
     * Form element type
     * @var string
     */
    protected $_type = 'password';

    /**
     * Field name for confirmation
     * @var string
     */
    protected $_confirmField;

    /**
     * Error message
     * @var string
     */
    protected $_errorMessage = 'Password doesn\'t match confirmation';

    /**
     * @param string $label
     * @param string $confirmField
     * @param string $description
     * @param bool $required
     * @param bool $notEdit
     * @param int $width
     */
    public function __construct(
        $label = null,
        $confirmField = 'password',
        $description = null,
        $required = true,
        $notEdit = false,
        $width = 200
    ) {
        parent::__construct($label, false, $description, $required, $notEdit, $width);

        $this->_confirmField = $confirmField;
    }

    /**
     * Initialize field (used by extending classes)
     *
     * @return void
     */
    protected function _init()
    {
        if (null === $this->_id) {
            $this->_required = true;
        }
        parent::_init();

        $passwrodField = $this->_form->getFieldByKey($this->_confirmField);
        $passwrodField->addValidator([
            'validator' => 'Confirmation',
            'with' => $this->_key
        ]);

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
