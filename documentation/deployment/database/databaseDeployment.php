<?

include_once(__DIR__ . "./databaseDeploymentBackUp.php");
include_once(__DIR__ . "./databaseDeploymentSchemaUpdate.php");
include_once(__DIR__ . "./Queries.php");

use app\database\db;
use app\helpers\storage;
use db_checker\database\Queries;

function getDbConnection()
{
	$ds = storage::getInstance();

	[ 'dbConnection' => $dbConnection ] = parse_ini_file ($ds->apiSettings . "settings.ini", true);

	return [
		$dbConnection,
		new db(
			$dbConnection['host'],
			$dbConnection['user'],
			$dbConnection['password'],
			$dbConnection['database']
		),
		new db(
			$dbConnection['host'],
			$dbConnection['user'],
			$dbConnection['password'],
			'information_schema'
		)
	];
}

function getDatabaseTablesDetails(array $dbConnector)
{
	[ $dbConnection, $db, $dbInfo ] = $dbConnector;

	$dbTablesSettings = [ 'tablesData' => [], 'tablesSettings' => [] ];

	try {
		foreach (Queries::tables($dbInfo, $dbConnection['database']) as $table) {
			$dbTablesSettings['tablesSettings'][$table] = Queries::describeTab($db, $table);
			$dbTablesSettings['tablesData'][$table] = Queries::contentOfTab($db, $table);
		}
	}
	catch (\PDOException $e) {
		printArr($e);
	}
	
	return $dbTablesSettings;
}
