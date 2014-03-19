<?php
/**
 * @namespace
 */
namespace Engine\Tools;

/**
 * Class Arrays
 *
 * @category   Engine
 * @package    Tools
 */
class Crypt
{
    private $_salt;
    private $_key;

    private $_message = null;
    private $_source = null;
    private $_date = null;

    /**
     *  The constructor initializes the cryptography library
     * @param $salt string The encryption key
     * @return void
     */
    public function __construct($key, $saltWord)
    {
        $this->_key = $key;
        $this->_setSaltWord($saltWord);
    }

    /**
     * Set salt word code for crypt.
     *
     * @param string $saltWord
     * @return void
     */
    protected function _setSaltWord($saltWord)
    {
        $this->_salt = sha1($saltWord);
    }

    /**
     * Generates a hex string of $src
     *
     * @param string $source
     * @return string
     */
    public function encrypt($source)
    {
        $value = $this->_getMessage($source."|".time());
        $encrypt = static::encryptData($value, $this->_key, $this->_salt);

        return $encrypt;
    }

    /**
     * Return decrypted data
     *
     * @param string $source
     * @return string
     */
    public function decrypt($source)
    {
        return static::decryptData($source, $this->_key, $this->_salt);
    }

    /**
     * Generate source message.
     *
     * @param string $source
     * @return string
     */
    protected function _getMessage($source)
    {
        return $source."_".sha1($source);
    }

    /**
     * Chrecking encrypt data.
     *
     * @param string $source
     * @return bool
     */
    public function check($code)
    {
        $decrypt = static::decryptData($code, $this->_key, $this->_salt);
        $m_a = explode( "_", $decrypt);
        if (is_array($m_a) && !empty($m_a[0]) && !empty($m_a[1]) && sha1($m_a[0] ) == $m_a[1]) {
            $this->_message = explode('|', $m_a[0]);
            $this->_source = $this->_message[0];
            $this->_date = $this->_message [1];

            return true;
        }

        return false;
    }

    /**
     * Return source string.
     *
     * @return string
     */
    public function getSource()
    {
        return $this->_source;
    }

    /**
     * Return encrypt date.
     *
     * @return string
     */
    public function getDate()
    {
        return $this->_date;
    }

    /**
     * Crypt data
     *
     * @param string $data
     * @param string $key
     * @param string $iv
     * @return string
     */
    static function encryptData($data, $key, $iv)
    {
        return base64_encode(mcrypt_cbc(MCRYPT_RIJNDAEL_128, substr(base64_encode(mhash(MHASH_SHA256, $key)), 0, 32), trim($data), MCRYPT_ENCRYPT, substr(base64_encode(mhash(MHASH_MD5, $iv)), 0, 16)));
    }

    /**
     * Decrypt data
     *
     * @param string $data
     * @param string $key
     * @param string $iv
     * @return string
     */
    static function decryptData($data, $key, $iv)
    {
        $data = base64_decode($data);
        return trim(mcrypt_cbc(MCRYPT_RIJNDAEL_128, substr(base64_encode(mhash(MHASH_SHA256, $key)), 0, 32), trim($data), MCRYPT_DECRYPT, substr(base64_encode(mhash(MHASH_MD5, $iv)), 0, 16)));
    }
}