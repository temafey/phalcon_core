<?php
/**
 * @namespace
 */
namespace Engine\Model\Behavior;

/**
 * Trait AnnotationsInitializer
 *
 * @category   Engine
 * @package    Model
 * @subpackage Behavior
 */
trait Timestampable
{
    /**
     * @Column(type="datetime", nullable=true, column="creation_date")
     */
    public $creation_date;

    /**
     * @Column(type="datetime", nullable=true, column="modified_date")
     */
    public $modified_date;

    public function beforeCreate()
    {
        $this->creation_date = date('Y-m-d H:i:s');
    }

    public function beforeUpdate()
    {
        $this->modified_date = date('Y-m-d H:i:s');
    }
}