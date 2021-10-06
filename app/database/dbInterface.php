<?

namespace app\database;

interface dbInterface
{

    public function __construct(
        string $host,
        string $user,
        string $password,
        string $database,
        string $charset = 'utf8'
    );

    public function selection(
        string $query,
        array $params,
        string $fetch,
        string $fetchMethod
    ): array;

    public function modification(string $query, string $table, array $params): array;

    public function runTransaction(array $queries): array;
    
    public function prepareQueryParams(array $affectedColumns, array $data): array;

    public function prepareUpdateColumns(array $affectedColumns, string $lastValueColumn): array;

    public function addWhereColumns(array $queryData, string $queryParams, string $whereParams): array;

    public function executeQuery(
        string $query,
        array $params,
        int $all,
        int $assoc,
        int $dimension,
    ): array;

    public function getHostName(): string;
    public function getDbName(): string;
    public function getQuery(): string;

    public function beginTransaction();
    public function commit();
    public function errorCode();
    public function errorInfo();
    public function exec(string $statement);
    public function getAttribute(int $attribute): bool|int|string|array|null;
    public function getAvailableDrivers();
    public function inTransaction();
    public function lastInsertId();
    public function prepare();
    public function query();
    public function quote();
    public function rollBack();
    public function setAttribute();
}
