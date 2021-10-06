<?php

namespace app\response;

/**
 * Application response
 *
 * @property $statusCodes, Allowed response http codes list
 */
class resp
{
    public $statusCodes = [
        'OK' => 200,
        'Created' => 201,
        'Accepted' => 202,
        'Non-Authorative Information' => 203,
        'No Content' => 204,
        'Moved Permanently' => 301,
        'Moved Temporarily' => 302,
        'Bad Request' => 400,
        'Authorization Required' => 401,
        'Forbidden' => 403,
        'Not Found' => 404,
        'Method Not Allowed' => 405,
        'Not Acceptable' => 406,
        'Proxy Authentication Required' => 407,
        'Request Timed Out' => 408,
        'Conflicting Request' => 409,
        'Internal Server Error' => 500
    ];

    /**
     *  Send response
     *
     *  @param int $code
     *  @param array $data
     *
     *  send json http response
     */
    public function send(int $code, $data = [])
    {
        if (in_array($code, $this->statusCodes)) {
            http_response_code($code);
            header('Content-Type: application/json');
            echo json_encode(['data' => $data]);
        }
        
        exit;
    }
}
