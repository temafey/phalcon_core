<?php
/**
 * @namespace
 */
namespace Engine\Acl;

use Phalcon\Mvc\User\Component,
    Engine\Tools\Crypt;

/**
 * Class Dispatcher
 *
 * @category   Engine
 * @package    Acl
 */
class Authorizer extends Component
{
    CONST AUTH_EDENTITY = 'viewer';
    CONST AUTH_MODEL    = 'authModel';
    CONST AUTH_KEY      = 'authKey';
    CONST CRYPT_SALT    = 'cryptSalt';

    /**
     * @var \Engine\Acl\Viewer
     */
    protected $_viewer;

    /**
     * Auth model name
     * @var string
     */
    protected $_model;

    /**
     * Auth seesion key
     * @var string
     */
    protected $_key;

    /**
     * Error message
     * @var string
     */
    protected $_message;

    /**
     * Constructor
     *
     * @param array $options
     * @param \Phalcon\DiInterface $di
     * @throws \Engine\Exception
     */
    public function __construct(array $options, \Phalcon\DiInterface $di)
    {
        $this->setDi($di);

        $this->_viewer = $this->{static::AUTH_EDENTITY};

        if (!isset($options[static::AUTH_MODEL])) {
            throw new \Engine\Exception("Auth model not set");
        }
        if (!new $options[static::AUTH_MODEL] instanceof AuthModelInterface) {
            throw new \Engine\Exception("Auth model '".$options[static::AUTH_MODEL]."' not instance of 'AuthModelInterface'");
        }
        $this->_model = $options[static::AUTH_MODEL];
        $this->_key = $options[static::AUTH_KEY];
    }

    /**
     * Checks the user credentials
     *
     * @param array $credentials
     * @return bool
     */
    public function check($credentials)
    {
        // Check if the user exist
        $user = forward_static_call([$this->_model, 'findByCredentials'], $credentials);
        if (!$user) {
            $this->_message = 'Wrong user credentials';
            return false;
        }
        // Check the password
        if (!$this->security->checkHash($credentials['password'], $user->getPasswordCredential())) {
            $this->_message = 'Wrong user credentials';
            return false;
        }

        // Check if the user was flagged
        $this->checkUserFlags($user);

        // Check if the remember me was selected
        if (isset($credentials['remember']) && $credentials['remember']) {
            $this->createRememberEnviroment($user);
        }
        $this->_viewer->setId($user->getId());
        $this->_viewer->setRole($user->getRole());

        return true;
    }

    /**
     * Check remember me value
     *
     * @param $value
     * @return boolean
     */
    public function checkRememberMe($crypt)
    {
        try {
            $value = $this->crypt->decryptBase64($crypt);
        } catch (\Exception $e) {
            $this->_message = 'Wrong auth params';
            return false;
        }

        $userId = $this->cookies->get($this->_key)->getValue();
        if ($value == $userId) {
            $user = forward_static_call([$this->_model, 'findUserById'], trim($value));
            if ($user) {
                return true;
            }
        }
        return false;
    }

    /**
     * Creates the remember me environment settings the related cookies and generating tokens
     *
     * @param \Engine\Acl\AuthModelInterface $authModel
     */
    public function createRememberEnviroment(AuthModelInterface $authModel)
    {
        $expire = time() + 86400 * 8;
        $this->cookies->set($this->_key, $authModel->getId(), $expire, null, null, null, false);
    }

    /**
     * Check if the session has a remember me cookie
     *
     * @return boolean
     */
    public function hasRememberMe()
    {
        return $this->cookies->has($this->_key);
    }

    /**
     * Logs on using the information in the coookies
     *
     * @return boolean
     */
    public function loginWithRememberMe()
    {
        $userId = $this->cookies->get($this->_key)->getValue();
        $user = forward_static_call([$this->_model, 'findUserById'], $userId);
        if ($user) {
            // Check if the user was flagged
            $this->checkUserFlags($user);

            $this->_viewer->setId($user->getId());
            $this->_viewer->setRole($user->getRole());

            return true;
        }

        $this->_viewer->destroy();

        return false;
    }

    /**
     * Checks if the user is banned/inactive/suspended
     *
     * @param Engine\Acl\AuthModelInterface $user
     */
    public function checkUserFlags(AuthModelInterface $user)
    {

    }

    /**
     * Removes the user identity information from session
     *
     * @return boolean
     */
    public function remove()
    {
        if ($this->cookies->has($this->_key)) {
            $this->cookies->get($this->_key)->delete();
        }

        $this->_viewer->destroy();

        return true;
    }

    /**
     * Auths the user by his/her id
     *
     * @param int $id
     * @return boolean
     */
    public function authUserById($userId)
    {
        $user = forward_static_call([$this->_model, 'findUserById'], $userId);
        if ($user == false) {
            $this->_message = 'The user does not exist';
            return false;
        }

        $this->checkUserFlags($user);

        $this->_viewer->setId($user->getId());
        $this->_viewer->setRole($user->getRole());

        return true;
    }

    /**
     * Get the entity related to user in the active identity
     *
     * @return \Engine\Mvc\Model
     */
    public function getUser()
    {
        $userId = $this->_viewer->getId();
        if ($userId) {
            $user = forward_static_call([$this->_model, 'findUserById'], $userId);
            if ($user == false) {
                $this->_message = 'The user does not exist';
                return false;
            }

            return $user;
        }

        return false;
    }

    /**
     * Is user authenticate
     *
     * @return bool
     */
    public function isAuth()
    {
        $result = false;
        if (!$this->_viewer->getId()) {
            $this->_message = 'You not autorize';
            if ($this->hasRememberMe()) {
                $this->loginWithRememberMe();
                if ($this->_viewer->getId()) {
                    $this->_viewer->getRole();
                    $result = true;
                }
            }
        } else {
            $result = true;
        }

        return $result;
    }

    /**
     * Return error message
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->_message;
    }
}