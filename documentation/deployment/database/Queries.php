<?

namespace documentation\deployment\database;

use function documentation\deployment\database\columnsSettings\{getColumnQueryString, getColumnsQueryString};
use function documentation\deployment\database\compareDBs\getColumnsFieldNames;

/**
 * Queries library
 *
 */
class Queries
{
    /**
     *  @return tables contained in database
     */
    public static function tables($db, $dbName = NULL)
    {
        return $db->processQuery(
            "SELECT TABLE_NAME FROM TABLES WHERE TABLE_SCHEMA = ?", [$dbName], 1, 1, 1
        );
    }

    /**
     *  @return table description
     */
    public static function describeTab($db, $table)
    {
        return $db->processQuery("SHOW FULL COLUMNS FROM `$table`",NULL,1,1);
    }
    
    /**
     *  @return table content
     */
    public static function contentOfTab($db, $table)
    {
        return $db->processQuery("SELECT * FROM `$table`", NULL, 1, 1);
    }

    /**
     *  Create table in selected database
     */
    public static function createTab(
        $db,
        string $table,
        string $columns,
        string $engine,
        string $defaultCharset
    ) {
        $db->processQuery(
            "CREATE TABLE IF NOT EXISTS `$table` ($columns) ENGINE=$engine DEFAULT CHARSET=$defaultCharset"
        );

        return [
            $db->getQuery(),
            "DROP TABLE `$table`"
        ];
    }

    /**
     *  Delete table from selected database
     */
    public static function delTab(
        $db,
        string $table,
        string $engine,
        string $defaultCharset
    ) {
        $columns = getColumnsQueryString(Queries::describeTab($db, $table));
        $db->processQuery("DROP TABLE `$table`");

        return [
            $db->getQuery(),
            "CREATE TABLE IF NOT EXISTS `$table` ($columns) ENGINE=$engine DEFAULT CHARSET=$defaultCharset"
        ];
    }

    /**
     *  Insert columns into selected database table
     */
    public static function insertColumns($db, string $table, array $columns)
    {
        $insertColumnsResult = [];
        
        foreach ($columns as $column => $columnQueryString) {
            $db->processQuery("ALTER TABLE `$table` ADD $columnQueryString");
            $columnName = explode(chr(32), $columnQueryString)[0];
            $insertColumnsResult[] = [
                $db->getQuery(),
                "ALTER TABLE `$table` DROP `$columnName`"
            ];
        }

        return $insertColumnsResult;
    }

    /**
     *  Delete columns from selected database table
     */
    public static function deleteColumns($db, string $table, array $columns, array $tableColumns)
    {
        $deleteColumnsResult = [];
        $searchColumns = getColumnsFieldNames($tableColumns);

        foreach ($columns as $colIdx => $column) {
            $delColIdx = array_search($column, $searchColumns);
            $after = $delColIdx ? $tableColumns[$delColIdx - 1]['Field'] : null;
            $delColumnQuerystring = getColumnQueryString($tableColumns[$delColIdx], $after);
            $db->processQuery("ALTER TABLE `$table` DROP `$column`");
            $deleteColumnsResult[] = [
                $db->getQuery(),
                "ALTER TABLE `$table` ADD $delColumnQuerystring"
            ];
        }

        return $deleteColumnsResult;
    }

    /**
     *  Modify columns in selected database table
     */
    public static function modifyColumns($db, string $table, array $columns, array $tableColumns)
    {
        $modifyColumnsResult = [];
        $searchColumns = getColumnsFieldNames($tableColumns);

        foreach ($columns as $column => $columnQueryString) {
            $modifyColIdx = array_search($column, $searchColumns);
            $modifiedColumnQuerystring = getColumnQueryString($tableColumns[$modifyColIdx]);
            $db->processQuery("ALTER TABLE `$table` MODIFY $columnQueryString");
            $modifyColumnsResult[] = [
                $db->getQuery(),
                "ALTER TABLE `$table` MODIFY $modifiedColumnQuerystring"
            ];
        }

        return $modifyColumnsResult;
    }
}
