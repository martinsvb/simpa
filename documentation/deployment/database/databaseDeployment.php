<?

namespace documentation\deployment\database\databaseDeployment;

use documentation\deployment\database\Queries;

use app\database\db;
use app\helpers\storage;

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
