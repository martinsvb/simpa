<?php

namespace app\router;

use app\exception\excepRouter;
use app\response\resp;
use modules\_base\users\usersAuthorisation;

/**
 * Application router
 *
 * URLs:
 * - "/": base
 * - "/module": module base URL
 * - "/module/id/x": module URL with parameter (id - param name, x - param value)
 *
 * @property $_allowedMethods, Allowed request methods
 * @property $_modulesPath, Basic modules location path
 * @property $_method, Called request method
 * @property $_headers, Request headers
 * @property $_params, Retrieve URL's params
 * @property $_data, Retrieve request data sent by POST or PUT methods
 * @property $_action, Requested module controller
 */
class router
{
    private $_allowedMethods = [
        'GET', // Read
        'POST', // Create
        'PATCH', // Update
        'PUT', // Replace
        'DELETE' // Delete
    ];
    
    private $_modulesPath = 'modules/action/';
    
    private
    $_method,
    $_headers,
    $_params,
    $_data,
    $_action;
    
    /** Headers part
        Host:"www.spanielovasvj.cz"
        Origin:"http://localhost:3000"
        Referer:"http://localhost:3000/"
        User-Agent:"Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/57.0.2987.133 Safari/537.36"
        email:"svobodamartin@centrum.cz"
    */
    
    /**
     *  Set headers
     *  Recognize request method
     *  Retrieve URL params and data
     *  Run requested controller
     */
    public function __construct()
    {
        $this->_enableCors();
        
        $this->_method = $_SERVER['HTTP_X_HTTP_METHOD'] ?? $_SERVER['REQUEST_METHOD'];
        
        if (!in_array($this->_method, $this->_allowedMethods)) {
            throw new excepRouter("Unrecognized request method");
        }
        
        $this->_headers = getallheaders();
        
        $this->_params = $this->_getParams(mb_substr($_SERVER['REQUEST_URI'], mb_strlen('/api/')));
        
        if ($this->_method == 'POST' || $this->_method == 'PATCH' || $this->_method == 'PUT') {
            $this->_data = json_decode(file_get_contents('php://input'), true);
        }
        
        $this->_action = $this->_params ? array_shift($this->_params) : 'base';
        
        $this->_run();
    }
    
    /**
     *  Enable CORS requests
     */
    private function _enableCors()
    {
        $origin = isset($_SERVER['HTTP_ORIGIN'])
            ? $_SERVER['HTTP_ORIGIN']
            : "*";
        $headers = isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'])
            ? $_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']
            : "*";
        
        header("Access-Control-Allow-Origin: $origin");
        header('Access-Control-Allow-Credentials: true');
        header("Access-Control-Allow-Headers: $headers");
        header("Access-Control-Allow-Methods: GET, POST, PATCH, PUT, OPTIONS, DELETE");
        
        if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
            exit(0);
        }
    }
    
    /**
     *  Transform requsted URL string to array
     *
     *  @param string $url
     *
     *  @return [string]
     */
    private function _getParams($url)
    {
        $parsedPath = explode("/", parse_url($url)['path']);
        return array_filter($parsedPath, function($path) {
            return $path;
        });
    }
    
    /**
     *  Run requested controller
     */
    private function _run()
    {
        $contr = $this->_modulesPath.$this->_action;

        if (file_exists($this->_modulesPath.'authorize/'.$this->_action.'.php')) {
            $this->_checkAuth();
            $contr = $this->_modulesPath.'authorize/'.$this->_action;
        }
        
        if (file_exists("$contr.php")) {
            $this->_pairParams();
            
            $contr = preg_replace('#\/#', '\\', $contr);
            $contr = new $contr();
            $contr->{strtolower($this->_method)}($this->_params, $this->_data, $this->_headers);
        }
        else {
            throw new excepRouter("Unrecognized controller: $contr");
        }
    }

    /**
     *  Check user authorization for requested action
     */
    private function _checkAuth()
    {
        $userAuth = new usersAuthorisation();
        
        if (!$userAuth->checkAuth($this->_action, $this->_headers, $this->_method)) {
            $resp = new resp();
            $resp->send($resp->statusCodes['Forbidden'], ['auth' => 0]);
        }
    }
    
    /**
     *  Retrieve key value pairs from requested URL
     *
     *  Store result array in property $_params
     */
    private function _pairParams()
    {
        if ($this->_params) {
            if (count($this->_params) % 2 == 0) {
                for ($i=0; $i<count($this->_params); $i += 2) {
                    $this->_params[$this->_params[$i]] = $this->_params[$i + 1];
                    unset($this->_params[$i]);
                    unset($this->_params[$i + 1]);
                }
            }
            else {
                throw new excepRouter("Bad params arguments length");
            }
        }
    }
}
