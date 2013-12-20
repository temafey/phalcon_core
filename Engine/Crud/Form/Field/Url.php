<?php
/**
 * @namespace
 */
namespace Engine\Crud\Form\Field;

use Engine\Crud\Form\Field;

/**
 * Url field
 *
 * @category   Engine
 * @package    Crud
 * @subpackage Form
 */
class Url extends Field
{
    /**
     * Error message
     * @var string
     */
    protected $_errorMessage = 'Invalid url';

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
                //'pattern' => '/^(https?:\/\/)?([\w\.]+)\.([a-z]{2,6}\.?)(\/[\w\.]*)*\/?$/'
                'pattern' => '/^(http|https|ftp)\://[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(:[a-zA-Z0-9]*)?/?([a-zA-Z0-9\-\._\?\,\'/\\\+&amp;%\$#\=~])*$/'
            ]
        );
    }
}