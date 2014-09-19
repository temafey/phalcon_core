<?php
/**
 * @namespace
 */
namespace Engine\Crud\Form\Field;

use Engine\Crud\Form\Field,
    Phalcon\Security;

/**
 * Phone field
 *
 * @category   Engine
 * @package    Crud
 * @subpackage Form
 */
class Password extends Field
{
    /**
     * Form element type
     * @var string
     */
	protected $_type = 'password';

    /**
     * @var \Phalcon\Secutiry
     */
    protected $_security;

    /**
     * Crypt type
     * @var string
     */
    protected $_cryptType;

    /**
     * Crypt key template
     * @var string
     */
    protected $_keyTemplate;

    /**
     * Max string length
     * @var integer
     */
    protected $_length;

    /**
     * @param string $label
     * @param string $name
     * @param \Phalcon\Security $security
     * @param string $keyTemplate
     * @param int $length
     * @param string $description
     * @param string $cryptType
     * @param bool $required
     * @param bool $notEdit
     * @param int $width
     */
    public function __construct(
        $label = null,
        $name = null,
        Security $security = null,
        $keyTemplate = '{name}',
        $length = 8,
        $description = null,
        $cryptType = 'blowfish',
        $required = true,
        $notEdit = false,
        $width = 280
    ) {
		parent::__construct($label, $name, $description, $required, $notEdit, $width);

        $this->_security = $security;
        $this->_length = (int) $length;
		$this->_keyTemplate = $keyTemplate;
		$this->_cryptType = $cryptType;
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

        $this->_validators[] = [
            'validator' => 'StringLength',
            'options' => [
                'min' => $this->_length
            ]
        ];

	}

    /**
     * Return minimum chars length
     *
     * @return int
     */
    public function getMinLength()
    {
        return $this->_length;
    }

    /**
     * Return field save data
     *
     * @return array|bool
     */
    public function getSaveData()
    {
        if ($this->_notSave) {
            return false;
        }
        $value = ($this->_security) ? $this->getHashValue() : $this->getCryptValue();

        return ['key' => $this->getName(), 'value' => $value];
    }

    /**
     * Return hashed password
     *
     * @return string
     */
    public function getHashValue()
    {
        return $this->_security->hash($this->_element->getValue());
    }

    /**
     * Return crypt password
     *
     * @return string
     */
    public function getCryptValue()
	{
        //Create an instance
        $crypt = new \Phalcon\Crypt();
        $crypt->setCipher($this->_cryptType);

        $key = \Engine\Tools\String::generateStringTemplate($this->_keyTemplate, $this->_form->getData(), '{', '}');
        $value = $this->_element->getValue();

        return $crypt->encryptBase64($value, $key);
	}

    /**
     * Return decrypt password
     *
     * @return string
     */
    public function getDecryptValue()
    {
        //Create an instance
        $crypt = new \Phalcon\Crypt();
        $crypt->setCipher($this->_cryptType);

        $key = \Engine\Tools\String::generateStringTemplate($this->_keyTemplate, $this->_form->getData(), '{', '}');
        $value = $this->_value;

        return $crypt->decryptBase64($value, $key);
    }
}
