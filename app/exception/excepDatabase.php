<?php

namespace app\exception;

use PDOException;
use app\exception\excep;

/**
 * Custom database exception
 *
 * @property $code, Exception code
 * @property $e, PDO Exception object
 * @property $errorInfo, Error information
 * @property $file, File where exception occured
 * @property $line, Line of wrong code
 * @property $message, Exception message
 * @property $_type, Type of exception
 */
class excepDatabase extends PDOException
{
    protected
    $e;
    
    private
    $_type = "Database";
    
    public function __construct(PDOException $e = null, $query = null, $params = null)
    {
        $this->e = $e;
        $excep = new excep();
        $excep->handle($this, ['query' => $query, 'params' => $params]);
    }
    
    public function getType()
    {
        return $this->_type;
    }
}
