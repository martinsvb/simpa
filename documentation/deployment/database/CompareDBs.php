<?

include_once(__DIR__ . "/columnsSettings.php");

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

function addColumnField(array $acc, array $item) {
    return [...$acc, $item['Field']];
}

/**
 * Retrieve missing columns in table
 */
function getMissingColumns(array $sourceCols, array $targetCols): array | null
{
    $missingColumns = null;

    $newColumns = array_reduce($sourceCols, 'addColumnField', []);
    $searchColumns = array_reduce($targetCols, 'addColumnField', []);

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

    $newColumns = array_reduce($sourceCols, 'addColumnField', []);
    $searchColumns = array_reduce($targetCols, 'addColumnField', []);

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
