<?php

namespace app\exception;

use Exception;

/**
 * Custom router exception
 *
 * @property $code, Exception code
 * @property $file, File where exception occured
 * @property $line, Line of wrong code
 * @property $message, Exception message
 * @property $_type, Type of exception
 */
class excepRouter extends Exception
{
    private
    $_type = "Router";
    
    public function __construct($message, $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
    
    public function getType()
    {
        return $this->_type;
    }
}
