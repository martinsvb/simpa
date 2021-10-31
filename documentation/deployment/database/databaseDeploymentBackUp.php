<?

namespace documentation\deployment\database\databaseDeploymentBackUp;

use function documentation\deployment\database\databaseDeployment\{getDbConnection, getDatabaseTablesDetails};

use app\helpers\csv;
use app\helpers\folders;
use app\helpers\storage;

function dbBackUp(string $dbBackUpFolder)
{
	$dbBackUpStatus = 'started';
	
	$ds = storage::getInstance();

	$folders = new folders($ds);

	[ $folderCreated, $folderExists ] = $folders->createFolder(
		$dbBackUpFolder,
		$folders->allowedPermissions['Owner_read_write_Others_read'],
		true
	);

	if ($folderCreated || $folderExists) {
		$dbConnector = getDbConnection();
		$csv = new csv();

		[
			'tablesData' => $tablesData,
			'tablesSettings' => $tablesSettings
		] = getDatabaseTablesDetails(
			$dbConnector
		);

		foreach ($tablesSettings as $table => $columns) {
			if ($columns && count($columns)) {
				$tableSettingsFileName = "$dbBackUpFolder\/table_".$table."_settings.csv";
				$csv->addData($tableSettingsFileName, array_keys($columns[0]));		
				$csv->addMultilineData($tableSettingsFileName, $columns);
			}
		}

		foreach ($tablesData as $table => $data) {
			if ($data && count($data)) {
				$tableDataFileName = "$dbBackUpFolder\/table_".$table."_data.csv";
				$csv->addData($tableDataFileName, array_keys($data[0]));
				$csv->addMultilineData($tableDataFileName, $data);
			}
		}

		$dbBackUpStatus = 'success';
	}
	else {
		$dbBackUpStatus = 'failed';
	}
	
	return $dbBackUpStatus;
}
