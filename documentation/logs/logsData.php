<?

namespace documentation\logs\logsData;

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
        $csv->readCsvData($ds->apiData['logs'] . "exceptions.csv"),
        $files->openFile(
            $ds->apiData['logs'] . "error.log",
            $files->openingMode['readOnly']
        )->read(),
    ];
}
