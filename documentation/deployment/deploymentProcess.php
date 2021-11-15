<?

namespace documentation\deployment\deploymentProcess;

use function documentation\deployment\database\databaseDeploymentBackUp\dbBackUp;
use function documentation\deployment\database\databaseDeploymentSchemaUpdate\dbSchemaUpdate;
use function documentation\generator\docView\{printHeader, printProperty};

use app\helpers\csv;
use app\helpers\folders;
use app\helpers\storage;

const DEPLOYMENT_RESULT = "deployment_result_";

function deploymentProcess(string | null $deploymentOperation, array $docDatabaseTables)
{
	$ds = storage::getInstance();
	
	$deleteAfterDeployment = [];
	
	$folders = new folders($ds);
	[ 'folders' => $deploymentFolders ] = $folders->readFullFolder($ds->apiData['deployments']);
	if ($deploymentOperation === 'processDeployment') {
		
		[ 'deploymentId' => $deploymentId ] = $_POST;
		
		if (
			isset($deploymentId) &&
			!in_array($deploymentId, $deploymentFolders) &&
			(isset($_POST['dbBackUp']) || isset($_POST['dbSchemaUpdate']))
		) {
			$deploymentResult = [
				'deploymentId' => $deploymentId,
				'dateTime (UTC)' => $ds->time['dateTimeString'],
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

			$deploymentResultFileName = DEPLOYMENT_RESULT . "$deploymentId.csv";

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
			
			printHeader('Deployment result', 3);
			foreach ($deploymentResult as $deploymentProperty => $deploymentPropertyValue) {
				printProperty($deploymentProperty, $deploymentPropertyValue, 1, 1, []);
			}
		}
		else if (!isset($_POST['dbBackUp']) && !isset($_POST['dbSchemaUpdate'])) {
			echo "<p>Deployment wasn't processed.<br />
			Please, select desired deployment's operations.</p>";
		}
		else {
			echo "<p>Deployment id: $deploymentId was already processed.<br />
			Please generate new id and start it again.</p>";
		}
	}
}
