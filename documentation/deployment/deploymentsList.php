<?

namespace documentation\deployment\deploymentsList;

use const documentation\deployment\deploymentProcess\DEPLOYMENT_RESULT;

use function documentation\generator\docView\printHeader;

use app\exception\excep;
use app\helpers\csv;
use app\helpers\files;
use app\helpers\folders;
use app\helpers\storage;

function getDeploymentsData(): array {

	$ds = storage::getInstance();

	$iterator = new \RecursiveIteratorIterator(
		new \RecursiveDirectoryIterator(
			$ds->apiData['deployments'],
			\RecursiveDirectoryIterator::SKIP_DOTS
		),
		\RecursiveIteratorIterator::SELF_FIRST
	);

	$excep = new excep();
	$files = new files($ds, $excep);
	$csv = new csv();

	$deploymentsData = [];

	foreach ($iterator as $fileinfo) {
		$fileName = $fileinfo->getFilename();
		if (
			$files->isExtension($fileName, ['csv']) &&
			preg_match('/^' . DEPLOYMENT_RESULT . '/', $fileName) &&
			$deploymentId = mb_substr(preg_replace('/' . DEPLOYMENT_RESULT . '/', NULL, $fileName), 0, -4)
		) {
			$deploymentsData[$deploymentId] = $csv->readCsvDataWithHeader(
				$fileinfo->getPath() . '/' . $fileName
			)[0];
		}
	}

	return array_reverse($deploymentsData);
}
