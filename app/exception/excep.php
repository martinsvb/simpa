<?php

namespace app\exception;

use app\response\resp;
use app\helpers\csv;
use app\helpers\storage;

/**
 *  Application exception handler
 */
class excep
{
    /**
     *  Log exception
     *  Send exception info response
     *
     *  @param object $e, Injected exception object
     *  @param array $addInfo, Additional exception info
     */
    public function handle($e, $addInfo = [])
    {
        $class = mb_substr(preg_replace("#".$_SERVER['DOCUMENT_ROOT']."/api/#", NULL, $e->getFile()), 0, -4);
        $class = preg_replace('#\/#', '\\', $class);
        
        $ds = storage::getInstance();

        $excepInfo = [
            'dateTime' => $ds->time['dateTimeString'],
            'type' => $e->getType(),
            'code' => $e->getCode(),
            'file' => $class,
            'line' => $e->getLine(), 
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ];

        if ($addInfo) {
            $excepInfo = $excepInfo + $addInfo;
        }

        $csv = new csv();
        $csv->addData($ds->apiData['logs'] . "exceptions.csv", $excepInfo);

        $resp = new resp();
        $resp->send($resp->statusCodes['Internal Server Error'], $excepInfo);
    }
}
