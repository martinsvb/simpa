<?

namespace documentation\generator\docDatabaseTables;

use app\helpers\csv;
use app\helpers\files;
use app\helpers\storage;
use app\exception\excep;

CONST TABLE_COLUMNS = [
    'Field',
    'Type',
    'Collation',
    'Null',
    'Key',
    'Default',
    'Extra',
    'Privileges',
    'Comment',
];

function getDatabaseTablesDocumentation()
{
    try {
        
        $docDatabaseTables = [];

        $ds = storage::getInstance();

        $databaseTablesLocation = $ds->apiModules . '__databaseTables';

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(
                $databaseTablesLocation,
                \RecursiveDirectoryIterator::SKIP_DOTS
            ),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        $excep = new excep();
        $files = new files($ds, $excep);
        $csv = new csv();

        foreach ($iterator as $fileinfo) {
            $fileName = $fileinfo->getFilename();
            $tableName = mb_substr($fileName, 0, -4);
            if ($files->isExtension($fileName, ['csv'])) {
                $docDatabaseTables[$tableName] = $csv->readCsvDataWithHeader(
                    $fileinfo->getPath() . '/' . $fileName
                );
            }
        }

        return $docDatabaseTables;
    }
    catch (\Exception $e) {
        echo "Directory iterator exception: " . $e->getMessage();
    }
}
