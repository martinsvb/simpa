<?php

namespace app\helpers;

class storage
{
    private
    $data = [];
    
    private static $instance;
    
    /**
     *  Singleton instantiate
     */
    public static function getInstance()
    {
        if (!self::$instance instanceof self) {
            self::$instance = new self();
        }
        
        return self::$instance;
    }
    
    public function __set( $name, $value )
    {
        $this->data[$name] = $value;
    }
    
    public function __get( $name )
    {
        if (isset($this->data[$name])) {
            return $this->data[$name];
        }
        
        $trace = debug_backtrace();
        trigger_error(
            'Undefined property via __get(): ' . $name .
            ' in ' . $trace[0]['file'] .
            ' on line ' . $trace[0]['line'],
            E_USER_NOTICE);
        
        return null;
    }
    
    public function __isset($name)
    {
        return isset($this->data[$name]);
    }
    
    public function __unset($name)
    {
        unset($this->data[$name]);
    }
    
    public function saveItem($point, $name)
    {
        $_SESSION[$point][] = $this->data[$name];
    }
    
    public function saveExternalData($point, $data)
    {
        $_SESSION[$point][] = $data;
    }
    
    public function getData()
    {
        return $this->data;
    }
    
    public function get_items_string()
    {
        return join(",", $this->data);
    }

    public function getUniqueId(int $length = 20)
    {
        if (function_exists("random_bytes")) {
            $bytes = random_bytes(ceil($length / 2));
        } elseif (function_exists("openssl_random_pseudo_bytes")) {
            $bytes = openssl_random_pseudo_bytes(ceil($length / 2));
        } else {
            throw new Exception("no cryptographically secure random function available");
        }
    
        return substr(bin2hex($bytes), 0, $length);
    }
}
