<?

namespace documentation\deployment\database\compareDBs;

use function documentation\deployment\database\columnsSettings\getColumnQueryString;

/**
 * Retrieve missing, new and same tables names
 */
function getTablesInfo(array $docDatabaseTables, array $tablesDetails): array
{
    $codeTables = array_keys($docDatabaseTables);
    $databaseTables = array_keys($tablesDetails);
    
    return [
        array_diff($codeTables, $databaseTables),
        array_diff($databaseTables, $codeTables),
        array_intersect($codeTables, $databaseTables),
    ];
}

/**
 * Same tables columns comparison
 */
function getColumnsQueries(array $sameTables, array $docDatabaseTables, array $tablesDetails): array
{
    $addColumns = [];
    $delColumns = [];
    $sameColumns = [];

    foreach ($sameTables as $table) {
        if ($addCols = getMissingColumns($docDatabaseTables[$table], $tablesDetails[$table])) {
            $addColumns[$table] = $addCols;
        }
        if ($delCols = getMissingColumns($tablesDetails[$table], $docDatabaseTables[$table])) {
            $delColumns[$table] = $delCols;
        }
        if ($sameCols = getSameColumnsDiff($docDatabaseTables[$table], $tablesDetails[$table])) {
            $sameColumns[$table] = $sameCols;
        }
    }

    foreach ($addColumns as $table => & $columns) {
        foreach ($columns as $colIdx => & $column) {
            $after = $colIdx ? $docDatabaseTables[$table][$colIdx - 1]['Field'] : null;
            $column = getColumnQueryString($docDatabaseTables[$table][$colIdx], $after);
        }
    }

    return [
        $addColumns,
        $delColumns,
        $sameColumns,
    ];
}

function getColumnsFieldNames(array $columns): array {
    return array_reduce(
        $columns,
        function (array $acc, array $item): array {
            return [...$acc, $item['Field']];
        },
        []
    );
}

/**
 * Retrieve missing columns in table
 */
function getMissingColumns(array $sourceCols, array $targetCols): array | null
{
    $missingColumns = null;

    $newColumns = getColumnsFieldNames($sourceCols);
    $searchColumns = getColumnsFieldNames($targetCols);

    foreach ($newColumns as $colIdx => $column) {
        if (!in_array($column, $searchColumns)) {
            $missingColumns[$colIdx] = $column;
        }
    }
    
    return $missingColumns;
}

/**
 * Retrieve same columns settings difference
 */
function getSameColumnsDiff(array $sourceCols, array $targetCols): array | null
{
    $sameColumnsDiff = null;

    $newColumns = getColumnsFieldNames($sourceCols);
    $searchColumns = getColumnsFieldNames($targetCols);

    foreach ($newColumns as $newColIdx => $column) {
        if (in_array($column, $searchColumns)) {
            $targetColIdx = array_search($column, $searchColumns);
            if ($colDiff = array_diff($sourceCols[$newColIdx], $targetCols[$targetColIdx])) {
                $colDiff['Field'] = $colDiff['Field'] ?? $column;
                $colDiff['Type'] = $colDiff['Type'] ?? $sourceCols[$newColIdx]['Type'];
                $sameColumnsDiff[$column] = getColumnQueryString($colDiff);
            }
        }
    }

    return $sameColumnsDiff;
}
