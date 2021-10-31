<?

namespace documentation\deployment\database\columnsSettings;

/**
 * Prepare multi columns query string
 */
function getColumnsQueryString(array $tableColumns): string
{
    $colQueArr = [];

    foreach ( $tableColumns as $column ) {
        $colQueArr[] = getColumnQueryString($column, marked: true);
    }
    
    return implode(',', $colQueArr);
}

/**
 * Prepare single column query string
 */
function getColumnQueryString(array $column, string | null $after = null, bool $marked = false): string
{
    [ 'Field' => $Field ] = $column;
    
    $columnQueryString = $marked ? "`$Field` " : "$Field ";
    $columnQueryString .= $column['Type'];

    if (isset($column['Collation']) && mb_strlen($column['Collation'])) {
        [ 'Collation' => $Collation ] = $column;
        $characterSet = mb_substr($Collation, 0, mb_strpos($Collation, "_"));
        $columnQueryString .= " CHARACTER SET $characterSet COLLATE $Collation";
    }

    if (isset($column['Default']) && mb_strlen($column['Default'])) {
        [ 'Default' => $Default ] = $column;
        $columnQueryString .= " DEFAULT \"$Default\"";
    }

    if (isset($column['Null']) && mb_strlen($column['Null'])) {
        $columnQueryString .= $column['Null'] === "YES" ? " NULL" : " NOT NULL";
    }

    if (isset($column['Key']) && mb_strlen($column['Key'])) {
        $columnQueryString .= $column['Key'] === "PRI" ? " PRIMARY KEY" : "";
        $columnQueryString .= $column['Key'] === "UNI" ? " UNIQUE" : "";
    }

    if (isset($column['Extra']) && mb_strlen($column['Extra'])) {
        $columnQueryString .= $column['Extra'] ? " " . $column['Extra'] : "";
    }

    if (isset($column['Key']) && $column['Key'] === "MUL" ) {
        $columnQueryString .= ", ADD INDEX ( `$Field` )";
    }

    if ($after) {
        $columnQueryString .= " AFTER $after";
    }
    
    return $columnQueryString;
}
