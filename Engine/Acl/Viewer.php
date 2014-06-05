<?php
/**
 * @namespace
 */
namespace Engine\Acl;

/**
 * Class Viewer
 *
 * @category   Module
 * @package    Core
 * @subpackage Model
 */
class Viewer extends \Phalcon\Session\Bag
{
    /**
     * Default role name
     */
    CONST DEFAULT_ROLE = 'guest';

    /**
     * @param \Phalcon\DiInterface $di
     */
    public function __construct(\Phalcon\DiInterface $di = null)
    {
        parent::__construct('viewer');
        $this->setDi($di);
    }

    /**
     * Return viewer role name
     *
     * @return string
     */
    public function getRole()
    {
        return $this->get('role', self::DEFAULT_ROLE);
    }

    /**
     * Set acl role name
     *
     * @param string $role
     * @return
     */
    public function setRole($role)
    {
        $this->set('role', $role);
        return $this;
    }

    /**
     * Return viewer id
     *
     * @return string
     */
    public function getId()
    {
        $id = $this->get('id');
        if (!$id) {
            return false;
        }
        return $id;
    }

    /**
     * Set viewer id
     *
     * @param int $id
     * @return
     */
    public function setId($id)
    {
        $this->set('id', $id);
        return $this;
    }
} 