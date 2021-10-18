<?php

namespace app\exception;

use PDOException;
use app\exception\excep;

/**
 * Custom database exception
 *
 * @property $code, Exception code
 * @property $_type, Type of exception
 */
class excepDatabase
{
    protected
    $e;
    
    private
    $_type = "Database";
    
    public function __construct(PDOException $e = null, $query = null, $params = null)
    {
        $this->e = $e;
        $excep = new excep();
        $excep->handle($this, ['query' => $query, 'params' => implode(', ', $params)]);
    }
    
    public function getType()
    {
        return $this->_type;
    }

    public function getCode()
    {
        return (int)$this->e->getCode();
    }

    public function getFile()
    {
        return $this->e->getFile();
    }

    public function getLine()
    {
        return $this->e->getLine();
    }

    public function getMessage()
    {
        return $this->e->getMessage();
    }

    public function getTraceAsString()
    {
        return $this->e->getTraceAsString();
    }
}
