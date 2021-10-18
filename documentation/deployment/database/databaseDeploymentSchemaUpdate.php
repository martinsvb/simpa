<?

include_once(__DIR__ . "/CompareDBs.php");

include_once(__DIR__ . "/columnsSettings.php");

use app\helpers\csv;
use app\helpers\folders;
use app\helpers\storage;
use db_checker\database\Queries;
use db_checker\checker\CompareDBs;

function dbSchemaUpdate(array $docDatabaseTables, string $dbSchemaUpdateFolder)
{
	$dbSchemaUpdateStatus = 'started';

	$ds = storage::getInstance();

	$folders = new folders($ds);

	[ $folderCreated, $folderExists ] = $folders->createFolder(
		$dbSchemaUpdateFolder,
		$folders->allowedPermissions['Owner_read_write_Others_read'],
		true
	);

	if ($folderCreated || $folderExists) {

		$dbConnector = getDbConnection();

		[ 'tablesSettings' => $tablesSettings ] = getDatabaseTablesDetails($dbConnector);

		[ $addTables, $delTables, $sameTables ] = getTablesInfo($docDatabaseTables, $tablesSettings);

		[ $dbConnection, $db, $dbInfo ] = $dbConnector;
		[ 'engine' => $engine, 'defaultCharset' => $defaultCharset ] = $dbConnection;

		[
			$addColumns,
			$delColumns,
			$sameColumnsDiff
		] = getColumnsQueries($sameTables, $docDatabaseTables, $tablesSettings);

		$executedQueries = [];
		$rollbackQueries = [];

		try {
			foreach ($addTables as $table) {
				$columns = getColumnsQueryString($docDatabaseTables[$table]);
				[
					$execQuery,
					$rollBackQuery
				] = Queries::createTab($db, $table, $columns, $engine, $defaultCharset);
				$executedQueries[] = $execQuery;
				$rollbackQueries[] = $rollBackQuery;
			}

			foreach ($delTables as $table) {
				[ $execQuery, $rollBackQuery ] = Queries::delTab($db, $table, $engine, $defaultCharset);
				$executedQueries[] = $execQuery;
				$rollbackQueries[] = $rollBackQuery;
			}

			foreach ($addColumns as $table => $columns) {
				foreach (Queries::insertColumns($db, $table, $columns) as [ $execQuery, $rollBackQuery ]) {
					$executedQueries[] = $execQuery;
					$rollbackQueries[] = $rollBackQuery;
				}
			}

			foreach ($delColumns as $table => $columns) {
				foreach (
					Queries::deleteColumns($db, $table, $columns, $tablesSettings[$table]) as [
						$execQuery,
						$rollBackQuery
				]) {
					$executedQueries[] = $execQuery;
					$rollbackQueries[] = $rollBackQuery;
				}
			}

			foreach ($sameColumnsDiff as $table => $columns) {
				foreach (
					Queries::modifyColumns($db, $table, $columns, $tablesSettings[$table]) as [
						$execQuery,
						$rollBackQuery
				]) {
					$executedQueries[] = $execQuery;
					$rollbackQueries[] = $rollBackQuery;
				}
			}

			saveDbSchemaUpdateQueries($dbSchemaUpdateFolder, $executedQueries, $rollbackQueries);

			$dbSchemaUpdateStatus = 'success';
		}
		catch (\PDOException $e) {
			printArr($e);

			$csv = saveDbSchemaUpdateQueries($dbSchemaUpdateFolder, $executedQueries, $rollbackQueries);

			saveDbSchemaUpdateException($dbSchemaUpdateFolder, $csv, $ds, $e);

			try {
				foreach ($rollbackQueries as $rollbackQuery) {
					$db->processQuery($rollBackQuery);
				}
			}
			catch (\PDOException $e) {
				printArr($e);
				saveDbSchemaUpdateException($dbSchemaUpdateFolder, $csv, $ds, $e);
				$dbSchemaUpdateStatus = 'failed, no rollbacked changes';
			}
			$dbSchemaUpdateStatus = 'failed, rollbacked changes';
		}
	}
	else {
		$dbSchemaUpdateStatus = "failed, schema didn't change";
	}

	return $dbSchemaUpdateStatus;
}

function saveDbSchemaUpdateQueries(
	string $dbSchemaUpdateFolder,
	array $executedQueries,
	array $rollbackQueries,
) {

	$csv = new csv();
	$csv->addMultilineData(
		"$dbSchemaUpdateFolder\/dbSchemaUpdate_Executed_Queries.csv",
		$executedQueries
	);
	$csv->addMultilineData(
		"$dbSchemaUpdateFolder\/dbSchemaUpdate_Rollback_Queries.csv",
		$rollbackQueries
	);

	return $csv;
}

function saveDbSchemaUpdateException(
	string $dbSchemaUpdateFolder,
	$csv,
	$ds,
	\PDOException $e,
) {
	$excepInfo = [
		'dateTime' => $ds->time['dateTimeString'],
		'code' => $e->getCode(),
		'file' => 'databaseDeploymentSchemaUpdate',
		'line' => $e->getLine(), 
		'message' => $e->getMessage(),
		'trace' => $e->getTraceAsString(),
	];

	$csv->addData(
		"$dbSchemaUpdateFolder\/dbSchemaUpdate_Exception.csv",
		$excepInfo,
	);
}
