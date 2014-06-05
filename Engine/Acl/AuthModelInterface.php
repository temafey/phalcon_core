<?php
/**
 * @namespace
 */
namespace Engine\Acl;

interface AuthModelInterface
{
    /**
     * Return user by auth credentials
     *
     * @param array $credentials
     * @return \Engine\Mvc\Model
     */
    public static function findByCredentials(array $credentials);

    /**
     * Return user by id
     *
     * @param integer $id
     * @return \Engine\Mvc\Model
     */
    public static function findUserById($id);


    /**
     * Return login credential
     *
     * @return string
     */
    public function getLoginCredential();

    /**
     * Return password credential
     *
     * @return string
     */
    public function getPasswordCredential();

    /**
     * Return user id
     *
     * @return string
     */
    public function getId();

    /**
     * Return user role
     *
     * @return string
     */
    public function getRole();
}