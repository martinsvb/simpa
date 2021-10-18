<?

header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: no-cache, no-store, must-revalidate'); // HTTP 1.1.
header('Pragma: no-cache'); // HTTP 1.0.
header('Expires: 0'); // Proxies.

include_once(__DIR__ . "/docGenerator/docHeader.php");
include_once(__DIR__ . "/docGenerator/docCodeOutput.php");
include_once(__DIR__ . "/docGenerator/docDatabaseTablesOutput.php");
include_once(__DIR__ . "/deployment/deploymentForm.php");
include_once(__DIR__ . "/logs/logsOutput.php");

$deploymentOperation = isset($_POST['button']) && in_array($_POST['button'], array_keys(DEPLOYMENT_BUTTONS))
    ? DEPLOYMENT_BUTTONS[$_POST['button']]
    : null;

generateDocHeader($deploymentOperation ? 'deployment' : null);

$docDatabaseTables = generateDocDatabaseTablesOutput();

generateDocOutput($docDatabaseTables, $deploymentOperation ? 'hide' : null);

generateDeploymentForm($deploymentOperation ? null : 'hide', $deploymentOperation, $docDatabaseTables);

generateLogsOutput();
