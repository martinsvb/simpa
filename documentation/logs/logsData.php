<?

use app\helpers\csv;
use app\helpers\files;
use app\helpers\storage;
use app\exception\excep;

CONST LOGS_COLUMNS = [
    'DateTime',
    'Type',
    'Code',
    'File',
    'Line',
    'Message',
    'Trace',
];

function getLogsData()
{
    $ds = storage::getInstance();

    $excep = new excep();
    $files = new files($ds, $excep);
    $csv = new csv();

    return [
        $csv->readCsvData($ds->apiLocation . "_logs/log_exceptions.csv"),
        $files->openFile(
            $ds->apiLocation . "_logs/api_error.log",
            $files->openingMode['readOnly']
        )->read(),
        $files->openFile(
            $ds->apiLocation . "_logs/documentation_error.log",
            $files->openingMode['readOnly']
        )->read(),
    ];
}
