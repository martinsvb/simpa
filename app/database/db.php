<?

namespace app\database;

use PDO;
use app\exception\excepDatabase;

/**
 * Database operations wrapper
 *
 * @property $_options, Database connection options
 * @property $_fetchMethods, PDO fetch methods list
 * @property $_host, Host name
 * @property $_dbName, Database name
 * @property $_stmt, Current database connection statement
 * @property $_excep, Exception handler
 * @property $_queries, Transaction queries definition
 * @property $_affectedIds, Queries affected Ids list
 */
class db extends PDO
{
    private static
    
    $_options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_AUTOCOMMIT, false,
    ],
    
    $selectionQue = ['SELECT', 'SHOW'];
    
    private
    $_host,
    $_dbName,
    $_stmt,
    $_excep,
    $_queries,
    $_affectedIds;

    public $fetchMethods = [
        'assoc' => PDO::FETCH_ASSOC,
        'num' => PDO::FETCH_NUM,
        'column' => PDO::FETCH_COLUMN,
        'key_pair' => PDO::FETCH_KEY_PAIR,
        'unique' => PDO::FETCH_UNIQUE,
        'group' => PDO::FETCH_GROUP
    ];

    /**
     * Create DB connection
     * 
     * @param string $host
     * @param string $user
     * @param string $password
     * @param string $database
     * @param string $charset (optional)
     */
    public function __construct(
        string $host,
        string $user,
        string $password,
        string $database,
        string $charset = 'utf8'
    ) {
        try {
            parent::__construct(
                "mysql:host=$host;dbname=$database;port=3306;charset=$charset",
                $user,
                $password,
                self::$_options
            );
            
            $this->_host = $host;
            $this->_dbName = $database;
        } catch (\PDOException $e) {
            throw new excepDatabase($e);
        }
    }
    
    /**
     * Select query execution
     *
     * @param string $query
     * @param array $params
     * @param string $fetch
     * @param string $fetchMethod
     *
     * @return array $data
     */
    public function selection(
        string $query,
        array $params = [],
        string $fetch = 'fetchAll',
        $fetchMethod = PDO::FETCH_ASSOC
    ) {
        $data = [];
        
        try {
            $this->_stmt = $this->prepare($query);
            $this->_stmt->execute($params);
            $data = $fetch === 'fetchAll'
                ? $this->_stmt->fetchAll($fetchMethod)
                : $this->_stmt->fetch($fetchMethod);
        }
        catch (\PDOException $e) {
            throw new excepDatabase($e, $query, $params);
        }
        
        return $data;
    }
    
    /**
     * Insert/Update queries execution
     *
     * @param string $query
     * @param string $table
     * @param array $params
     *
     * @return array $_affectedIds
     */
    public function modification($query, $table, $params = [])
    {
        $this->_affectedIds = [];
        
        try {
            $this->exec("LOCK TABLES $table WRITE");
            
            $this->_stmt = $this->prepare($query);
            foreach ($params as $values) {
                $this->_stmt->execute($values);
                $this->_affectedIds[] = $this->lastInsertId();
            }
            
            $this->exec("UNLOCK TABLES");
        }
        catch(\PDOException $e) {
            $this->rollBack();
            throw new excepDatabase($e, $query, $params);
        }
        
        return $this->_affectedIds;
    }
    
    /**
     * Set of Insert/Update queries transaction execution
     *
     * @param array $queries, multidimensional array contains queries and their params, indexed by name
     *
     * @return array $_affectedIds
     */
    public function runTransaction($queries)
    {
        $this->queries = $queries;

        $this->_transactionDefinitionUpdate();

        try {
            $this->beginTransaction();
            $lockTables = array_map(
                function($table) {
                    return mb_substr(mb_strstr($table, '@'), 1) . " WRITE";
                },
                array_keys($this->queries)
            );
            $this->exec("LOCK TABLES " . implode(",", $lockTables));

            $this->_affectedIds = [];
            
            foreach ($this->queries as $name => $arr) {
                $action = mb_strstr($name, '@', true);
                $this->_stmt = $this->prepare($this->queries[$name]['query']);
                foreach ($this->queries[$name]['queryParams'] as $key => $values) {
                    $exeResult = $this->_stmt->execute($values);
                    if ($action == 'insert' && $exeResult) {
                        $this->_affectedIds[$name][] = $this->lastInsertId();
                    }
                    if ($action == 'update' && $exeResult) {
                        $this->_affectedIds[$name][] = $key;
                    }
                }
            }
            
            $this->exec("UNLOCK TABLES");
            $this->commit();
        }
        catch(\PDOException $e) {
            $this->rollBack();
            
            $queries = $queriesParams = [];
            
            foreach ($this->queries as $name => $arr) {
                $queries[] = $this->queries[$name]['query'];
                $queriesParams[] = $this->queries[$name]['queryParams'];
            }
            
            throw new excepDatabase($e, implode(";", $queries), $queriesParams);
        }
        
        return $this->_affectedIds;
    }

    /**
     *  Create transaction queries and param
     */
    private function _transactionDefinitionUpdate()
    {
        foreach ($this->queries as $name => & $arr) {
            $action = mb_strstr($name, '@', true);
            if (!in_array($action, ['insert', 'update', 'select'])) {
                return 'error';
            }
            
            $table = mb_substr(mb_strstr($name, '@'), 1);
            $arr['data'] = $this->_transactionDataMerge($arr);
            $arr['queryParams'] = $this->prepareQueryParams($arr['affectedColumns'], $arr['data']);
            
            if ($action == 'insert') {
                $arr['query'] = "INSERT INTO $table (" . implode(",", array_keys($arr['affectedColumns'])) . ") VALUES (" . implode(",", array_keys($this->queries[$name]['queryParams'][0])) . ")";
            }
            if ($action == 'update') {
                $arr['updateColumns'] = $this->prepareUpdateColumns($arr['affectedColumns']);
                $arr['queryParams'] = $this->addWhereColumns($arr['data'], $arr['queryParams'], $arr['where']);
                $arr['query'] = "UPDATE $table SET " . implode(",", $arr['updateColumns']) . " WHERE " . implode(" AND ", $arr['where']);
            }
        }
    }
    
    /**
     *  Process merge settings from queries definiton
     *
     *  - retrieve data from previous query
     *  - retrieve data from data source
     *
     *  @param array $arr, definition of current query
     *
     *  @return array $arr, modified data part of current query definition
     */
    private function _transactionDataMerge($arr = [])
    {
        if (isset($arr['merge']) && $arr['merge']) {
            foreach ($arr['data'] as $key => $value) {
                $linkQuery = $arr['merge']['mergeQueryAffId'];
                
                // Merge data from previous query result
                if (
                    !isset($arr['data'][$key][$arr['merge']['affectedColumn']]) &&
                    isset($this->_affectedIds[$linkQuery][$key])
                ) {
                    $arr['data'][$key][$arr['merge']['affectedColumn']] = $this->_affectedIds[$linkQuery][$key];
                }
                
                // Merge data from data source
                if (isset($arr['merge']['dataColumns']) && $arr['merge']['dataColumns']) {
                    foreach ($arr['merge']['dataColumns'] as $colInfo) {
                        $targetCol = mb_strstr($colInfo, '@', true);
                        $sourceCol = mb_substr(mb_strstr($colInfo, '@'), 1);
                        
                        if (!isset($arr['data'][$key][$targetCol]) && isset($arr['data'][$key][$sourceCol])) {
                            $arr['data'][$key][$targetCol] = $arr['data'][$key][$sourceCol];
                        }
                        
                        if (
                            !isset($arr['data'][$key][$targetCol]) &&
                            isset($this->queries[$linkQuery]['affectedColumns'][$sourceCol]) &&
                            $this->queries[$linkQuery]['affectedColumns'][$sourceCol] !== "%data%"
                        ) {
                            $arr['data'][$key][$targetCol] = $this->queries[$linkQuery]['affectedColumns'][$sourceCol];
                        }
                    }
                }
            }
        }
        
        return $arr['data'];
    }
    
    /**
     * Prepare query params regarding to affected columns and data
     *
     * @param array $affectedColumns
     * @param array $data
     *
     * @return array $queryParams
     */
    public function prepareQueryParams($affectedColumns = [], $data = [])
    {
        $queryParams = [];
        foreach ($data as $arrData) {
            $values = [];
            foreach ($affectedColumns as $column => $value) {
                $values[":$column"] = $value == "%data%" && isset($arrData[$column])
                    ? $arrData[$column]
                    : $value;
            }
            $queryParams[] = $values;
        }
        
        return $queryParams;
    }
    
    /**
     * Prepare list of updated columns names
     *
     * @param array $affectedColumns
     * @param string $lastValueColumn
     *
     * @return array $updateColumns
     */
    public function prepareUpdateColumns($affectedColumns=[], $lastValueColumn=null)
    {
        $updateColumns = [];
        foreach (array_keys($affectedColumns) as $column) {
            $updateColumns[] = "`$column`=:$column";
            if ($column == $lastValueColumn) {
                break;
            }
        }
        
        return $updateColumns;
    }
    
    /**
     * Add missing where statement columns from data to queryParams
     *
     * @param array $queryData
     * @param string $queryParams
     * @param string $whereParams
     *
     * @return array $queryParams
     */
    public function addWhereColumns($queryData = [], $queryParams = [], $whereParams = [])
    {
        foreach($queryData as $dataArr) {
            foreach ($queryParams as & $paramsArr) {
                foreach ($dataArr as $dataKey => $dataVal) {
                    foreach ($whereParams as $whereKey) {
                        if (
                            !in_array(":$dataKey", array_keys($paramsArr)) &&
                            $dataKey == mb_strstr($whereKey, "=", true)
                        ) {
                            $paramsArr[":$dataKey"] = $dataVal;
                        }                        
                    }
                }
            }
        }
        
        return $queryParams;
    }
    
    /**
     * Load data from db by inserted query
     *
     * @param string $query, MySQL query string
     * @param array $params, Array of query's parameters
     * @param int $all, setting for all query's items or only one item
     * @param int $assoc, result data array's keys ASSOC or NUM
     * @param int $dimension, update two dimensions result data array (if only one columns is loaded) to one dimension array
     *
     * @return array $data
     */
    public function executeQuery(
        string $query,
        array $params=NULL,
        int $all=0,
        int $assoc=0,
        int $dimension=0
    ) {
        $data = null;
        
        // Process query
        try {
            $data = $this->processQuery($query, $params, $all, $assoc, $dimension);
        } catch (\PDOException $e) {
            throw new excepDatabase($e, $query, $params);
        }
        
        return $data;
    }

    /**
     * Set and process query execution
     */
    public function processQuery(
        string $query,
        array $params=NULL,
        int $all=0,
        int $assoc=0,
        int $dimension=0
    ) {
        $data = null;

        $this->_stmt = $this->prepare($query);
        $params && count($params)
            ? $this->_stmt->execute($params)
            : $this->_stmt->execute();

        // All table's records or one concrete
        if (in_array(mb_substr($query, 0, mb_strpos($query, " ")), self::$selectionQue)) {
            $fetchMode = $assoc===1 ? PDO::FETCH_ASSOC : PDO::FETCH_NUM;
            $data = $all===1
                ? $this->_stmt->fetchAll($fetchMode)
                : $this->_stmt->fetch($fetchMode);
        }

        // Records contains only one column
        if (($dimension===1) && is_array($data[0]) && count($data[0]) == 1) {
            $data = $this->_indexSingleColumnData($data, $assoc);
        }

        return $data;
    }

    /**
     * Records contains only one column
     */
    private function _indexSingleColumnData(
        array $data,
        int $assoc,
    ) {
        foreach ($data as $key => $val) {
            foreach ($val as $k => $v) {
                if (($assoc===1)) {
                    unset($data[$key]);
                    $data[$k."-".$key] = $v;
                } else $data[$key] = $v;
            }
        }
        
        return $data;
    }

    public function getHostName()
    {
        return $this->_host;
    }
    
    public function getDbName()
    {
        return $this->_dbName;
    }
    
    public function getQuery() {
        return $this->_stmt->queryString;
    }
}
