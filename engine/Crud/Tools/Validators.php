<?php
/**
 * @namespace
 */
namespace Engine\Crud\Tools;

use Phalcon\Validation\ValidatorInterface;

/**
 * Trait Validators
 *
 * @category    Engine
 * @package     Crud
 * @subcategory Tools
 */
trait Validators
{

    /**
     * Validation object
     * @var \Engine\Validation
     */
    protected $_validation;

    /**
     * Field validators
     * @var array
     */
    protected $_validators = [];

    /**
     * Return field validators
     *
     * @return array
     */
    public function getValidators()
    {
        $validators = [];
        foreach ($this->_validators as $validator) {
            $validators[] = $this->createValidator($validator);
        }

        return $validators;
    }

    /**
     * Create validator
     *
     * @param $validator
     * @return \Phalcon\Validation\ValidatorInterface
     * @throws \Engine\Exception
     */
    public function createValidator($validator)
    {
        if ($validator instanceof ValidatorInterface) {
            return $validator;
        } elseif (is_array($validator)) {
            $origName = ucfirst($validator['validator']);
            if ($class = $this->getValidatorClassName($origName)) {
                if (empty($validator['options'])) {
                    $validator = new $class;
                } else {
                    $r = new \ReflectionClass($class);
                    if ($r->hasMethod('__construct')) {
                        $validator = $r->newInstance($validator['options']);
                    } else {
                        $validator = $r->newInstance();
                    }
                }
            }
        } elseif (is_string($validator)) {
            $origName = ucfirst($validator);
            if ($class = $this->getValidatorClassName($origName)) {
                $validator = new $class;
            }
        } else {
            throw new \Engine\Exception("Validator '$validator' not exists");
        }

        return $validator;
    }

    /**
     * Add a filter to the element
     *
     * @param  string|\Engine\Filter\FilterInterface|array
     * @return \Engine\Crud\Tools\Validators
     */
    public function addValidator($validator)
    {
        $this->_validators[] = $validator;

        return $this;
    }

    /**
     * Add Validators to element
     *
     * @param  array $validators
     * @return \Engine\Crud\Tools\Validators
     */
    public function addValidators(array $validators)
    {
        foreach ($validators as $validator) {
            $this->addValidator($validator);
        }

        return $this;
    }

    /**
     * Add Validators to element, overwriting any already existing
     *
     * @param  array $validators
     * @return \Engine\Crud\Tools\Validators
     */
    public function setValidators(array $validators)
    {
        $this->clearValidators();
        return $this->addValidators($validators);
    }

    /**
     * Clear all validators
     *
     * @return \Engine\Crud\Tools\Validators
     */
    public function clearValidators()
    {
        $this->_validators = [];
        return $this;
    }

    /**
     * Return validator class name
     *
     * @return string
     */
    public function getValidatorClassName($name)
    {
        $validator = '\Engine\Validation\Validator\\'.ucfirst($name);
        if (!class_exists($validator)) {
            $validator = '\Phalcon\Validation\Validator\\'.ucfirst($name);
            if (!class_exists($validator)) {
                return false;
            }
        }

        return $validator;
    }
}