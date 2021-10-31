<?

namespace documentation\deployment\deploymentProcess;

use function documentation\deployment\database\databaseDeploymentBackUp\dbBackUp;
use function documentation\deployment\database\databaseDeploymentSchemaUpdate\dbSchemaUpdate;

use app\helpers\csv;
use app\helpers\folders;
use app\helpers\storage;

function deploymentProcess(string | null $deploymentOperation, array $docDatabaseTables)
{
	$ds = storage::getInstance();
	
	$deleteAfterDeployment = [];
	
	$folders = new folders($ds);
	[ 'folders' => $deploymentFolders ] = $folders->readFullFolder($ds->apiData['deployments']);
	if ($deploymentOperation === 'processDeployment') {
		
		[ 'deploymentId' => $deploymentId ] = $_POST;
		
		if (isset($deploymentId) && !in_array($deploymentId, $deploymentFolders)) {
			$deploymentResult = [
				'deploymentId' => $deploymentId,
				'deploymentUtcDateTime' => $ds->time['dateTimeString'],
				'user' => $_SESSION['user'],
			];
			
			$deploymentFolder = $ds->apiData['deployments'] . $deploymentId;
	
			[ $folderCreated, $folderExists ] = $folders->createFolder(
				$deploymentFolder,
				$folders->allowedPermissions['Owner_read_write_Others_read'],
				true
			);

			if ($folderCreated || $folderExists) {
				if (isset($_POST['dbBackUp'])) {
					$deploymentResult['dbBakcUp'] = dbBackUp("$deploymentFolder/database_back_up",);
					$deleteAfterDeployment[] = "\database_back_up\.";
				}

				if (isset($_POST['dbSchemaUpdate'])) {
					$deploymentResult['dbSchemaUpdate'] = dbSchemaUpdate(
						$docDatabaseTables,
						"$deploymentFolder/database_schema_update",
					);
					$deleteAfterDeployment[] = "\database_schema_update\.";
				}
			}

			$deploymentResultFileName = "deployment_result_$deploymentId.csv";

			$csv = new csv();
			$csv->addMultilineData(
				"$deploymentFolder\/$deploymentResultFileName",
				[ 'title' => array_keys($deploymentResult), $deploymentResult ]
			);

			$folders->zipFolder(
				$deploymentFolder,
				"deployment_$deploymentId",
				$deleteAfterDeployment
			);
			printArr($deploymentResult);
		}
		else {
			echo "<p>Deployment id: $deploymentId was already processed.<br />
			Please generate new id and start it again.</p>";
		}
	}
}
