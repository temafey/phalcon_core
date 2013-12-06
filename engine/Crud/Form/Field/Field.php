<?php
/**
 * @namespace
 */
namespace Engine\Crud\Form\Field;

/**
 * Interface of grid filter field
 *
 * @category   Engine
 * @package    Crud
 * @subpackage Form
 */
interface Field 
{
    /**
     * Return field save data
     *
     * @return array|bool
     */
    public function getSaveData();

    /**
     * Set field value
     *
     * @param string|integer|array $value
     */
    public function setValue($value);

    /**
     * Return field key
     *
     * @return string
     */
    public function getKey();

    /**
     * Return field name
     *
     * @return string
     */
    public function getName();

    /**
     * Return phalcon form element
     *
     * @return \Phalcon\Forms\Element
     */
    public function getElement();

    /**
     * Update filter field
     *
     * @return mixed
     */
    public function updateField();

}