<?php
/**
 * @namespace
 */
namespace Engine;

/**
 * Class Exception
 *
 * @category   Engine
 * @package    Exception
 */
class Exception extends \Exception
{
    /**
     * Inject logging logic
     * @see Exception
     */
    public function __construct($message = '', $code = 0, Exception $previous = null)
    {
        parent::__construct((string)$message, (int)$code, $previous);
        Error::exception($this);
    }


}
